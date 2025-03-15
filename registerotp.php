<?php
if (!isset($_SESSION)) session_start();

const _DEFVAR = 1;

// Include config file
require_once "config.php";

include 'vendor\autoload.php';

use PragmaRX\Google2FA\Google2FA;
use chillerlan\QRCode\{QRCode, QROptions};

if (!isset($_SESSION["id"]) || empty((int)$_SESSION["id"])) {
    // não conseguiu pegar o id do usuário
    header("location: error.php");
    exit;
}

$user_id = (int)$_SESSION["id"];

// verificar se o usuário já possui um otp_secret armazenado no banco
// Prepare a select statement
$sql = "SELECT otp_secret FROM users WHERE id = :id";

// For SQLite3, use a different parameter binding approach
$stmt = $link->prepare($sql);

// SQLite3 uses different binding method
$stmt->bindValue(':id', $user_id, PDO::PARAM_INT);

// Execute the prepared statement
$stmt->execute();

if ($stmt->execute()) {

    $row = $stmt->fetch();
    $otp_secret = $row["otp_secret"];

    $google2fa = new Google2FA();

    // se não tiver, precisa gera um novo
    if (empty($otp_secret)) {

        // gerando um otp_secret
        $otp_secret = $google2fa->generateSecretKey();

        // gerando o primeiro timestamp para impedir de usar o código duas vezes
        $ts = $google2fa->getTimestamp();

        // atualizar o banco para registrar o novo otp_secret
        $sql = "UPDATE users SET otp_secret = :otp_secret, otp_ts = :ts WHERE id = :id";

        $stmt = $link->prepare($sql);
        // Bind variables to the prepared statement as parameters
        $stmt->bindValue(':otp_secret', $otp_secret, PDO::PARAM_STR);
        $stmt->bindValue(':ts', $ts, PDO::PARAM_INT);
        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);

        // Attempt to execute the prepared statement
        if (!$stmt->execute()) {
            // não conseguiu atualizar
            header("location: error.php");
            exit;
        }

    }

    // gerando os dados para o qrcode
    $qrCodeUrl = $google2fa->getQRCodeUrl(
        'Company Name',
        'company@email.com',
        $otp_secret
    );


    // gerando a url que direciona para o qrcode
    $qrcode_url = (new QRCode)->render($qrCodeUrl);

    // pesquisar no banco os códigos de backup que o usuário já possa ter gerado
    $sql = "SELECT backup_code FROM backupcodes WHERE cod_user = :id AND used = 0";

    // For SQLite3, use a different parameter binding approach
    $stmt = $link->prepare($sql);

    // SQLite3 uses different binding method
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);

    $recovery_codes = [];

    if ($stmt->execute()) {

        // convertendo o resultado para um array
        $result = $stmt->fetchAll();

        // contando para ver quantos retornaram
        $rows = count($result);

        // verficar se retornou algum item
        if ($rows > 0) {

            foreach ($result as $row) {
                $recovery_codes[] = $row["backup_code"];
            }

        } else {

            // inciando a transaction para otimizar os inserts
            $link->beginTransaction();

            // for para 8 códigos de recuperação
            for ($i = 0; $i < 8; $i++) {

                // gerando um randômico de 1000 até 99999999 para evitar
                // de ser um número muito pequeno
                $randon = random_int(1000, 99999999);

                // formatando o número para preencher com zeros à esquerda
                $recovery_codes[$i] = sprintf("%'.08d", $randon);

                // Prepare an insert statement
                $sql = "INSERT INTO backupcodes (cod_user, backup_code) VALUES (:cod_user, :backupcode)";

                $stmt = $link->prepare($sql);

                // Set parameters
                $cod_user = $user_id;
                $backupcode = $recovery_codes[$i];

                // Bind variables to the prepared statement as parameters
                $stmt->bindValue(":cod_user", $cod_user, PDO::PARAM_STR);
                $stmt->bindValue(":backupcode", $backupcode, PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if (!$stmt->execute()) {
                    //print_r($stmt);
                    $link->rollBack();
                    header("location: error.php");
                    exit();
                }
            }
            $link->commit();
        }

    } else {
        header("location: error.php");
        exit;
    }

} else {
    header("location: error.php");
    exit;
}

disconnectDataBase();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<div class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="mt-5 mb-3 clearfix text-center">
                    <h2>Chave OTP e códigos de recuperação</h2>
                    <p>Utilize seu aplicativo autenciador</p>
                    <p>Chave OTP: <?php echo $otp_secret; ?></p>
                    <p><img src="<?php echo $qrcode_url ?>" alt="" height="200px"></p>

                    <?php if (!empty($recovery_codes)) { ?>
                        <h3>Anote os códigos de recuperação abaixo. Eles não serão exibidos novamente!</h3>
                        <?php
                        for ($i = 0; $i < count($recovery_codes); $i++) {
                            echo "<p>" . $recovery_codes[$i] . "</p>";
                        }
                        ?>
                        <?php
                    }
                    ?>
                    <p>
                        <a href="login.php" class="btn btn-primary">Ir para login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="js/bootstrap.bundle.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
