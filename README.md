# Sistema de autenticação com 2FA e assinatura digital de registros

## O que foi feito?

Desenvolvi um app que de login que utiliza 2º fator de autenticação, verifica a complexidade de senha no cadastro e cria códigos de backup para caso o usuário não consiga utilizar o 2FA.

Foi utilizado o recurso da biblioteca Google2FA que impede que o mesmo OTP seja utilizado 2 vezes na mesma janela de tempo.

Também foi utilizado uma biblioteca para assinatura digital dos registros e verificar se os dados foram alterados diretamente no banco de dados, com o sistema indicando que a assinatura não é válida.


## Bibliotecas utilizadas
- pragmarx/google2fa - para os recursos de 2FA TOTP
- chillerlan/php-qrcode - para gerar o QRCode para os autenticadores 2FA
- phpseclib/phpseclib - biblioteca para os recursos de assinatura digital
- CipherHelper - Helper para cifragem/decifragem de dados
- PasswordStrengthValidatorHelper - Helper para verificação de complexidade de senha
