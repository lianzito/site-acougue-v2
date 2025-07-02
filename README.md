**Açougue Nosso - E-commerce em PHP**

Este projeto é um sistema de e-commerce completo para um açougue fictício, o "Açougue Nosso", com uma tradição que remonta a 1985. O sistema foi desenvolvido em PHP puro, focado em ser uma solução funcional e didática para demonstrar a criação de uma loja virtual com integração a um gateway de pagamento real.

**Principais Funcionalidades**
- Visualização de Produtos: Navegação por produtos com um sistema de filtragem por categorias (Bovinos, Suínos, Aves).

- Carrinho de Compras: Funcionalidades completas de um carrinho, permitindo adicionar, remover e atualizar a quantidade de itens de forma dinâmica e interativa.

- Sistema de Usuários: Cadastro e login de clientes, com validação de CPF para o mercado brasileiro e uma área de "Minha Conta".

- Histórico de Pedidos: Os clientes podem visualizar todos os seus pedidos anteriores com o status atualizado (Pendente, Pago, Cancelado).

- Integração de Pagamento com Asaas: Checkout transparente que gera links de pagamento dinâmicos via API da Asaas, permitindo que o cliente escolha entre PIX, Boleto e Cartão de Crédito.

- Webhook Automatizado: O sistema possui um endpoint de webhook para receber notificações da Asaas em tempo real, atualizando o status dos pedidos no banco de dados automaticamente assim que um pagamento é confirmado.

**Tecnologias Utilizadas**
- Backend: PHP 8+ (estilo procedural com a extensão mysqli para comunicação com o banco de dados).

- Frontend: HTML5, CSS3 e JavaScript puro, utilizando a biblioteca SweetAlert2 para criar alertas e notificações amigáveis ao usuário.

- Banco de Dados: MySQL. O arquivo de schema e dados iniciais está disponível em acougue_db.sql.

- Gateway de Pagamento: API REST da Asaas para gestão de clientes e cobranças.

**Como Executar o Projeto Localmente**
- Certifique-se de ter um ambiente de desenvolvimento local como XAMPP, WAMP ou Laragon.

- Clone ou baixe este repositório para a pasta do seu servidor web (ex: htdocs).

- Crie um banco de dados com o nome acougue_db no seu gestor de banco de dados (phpMyAdmin, por exemplo).

- Importe o arquivo acougue_db.sql para o banco de dados recém-criado.

- Abra o arquivo config.php e, se necessário, altere as credenciais de acesso ao seu banco de dados local.

- Ainda em config.php, insira sua chave de API do ambiente Sandbox da Asaas na constante ASAAS_API_KEY.

- Para testar o webhook, utilize uma ferramenta como o ngrok para expor seu servidor local à internet e configure a URL gerada no painel da Asaas.

**Criado por Marcelo Silva de Paula Filho e Lian Fantucci**
