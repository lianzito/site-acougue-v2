-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02/07/2025 às 13:31
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `acougue_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_asaas_cobranca` varchar(255) NOT NULL,
  `id_asaas_pagamento` varchar(255) DEFAULT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `link_pagamento` varchar(255) NOT NULL,
  `itens_pedido` text NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `id_usuario`, `id_asaas_cobranca`, `id_asaas_pagamento`, `valor_total`, `status`, `link_pagamento`, `itens_pedido`, `data_criacao`) VALUES
(13, 8, 'pxpar29mhplpgb0n', 'pay_91kp99d13zfjxbht', 123.50, 'RECEIVED', 'https://sandbox.asaas.com/c/pxpar29mhplpgb0n', '[{\"nome\":\"Contrafil\\u00e9 (Pe\\u00e7a)\",\"quantidade\":1,\"preco_unitario\":\"65.50\"},{\"nome\":\"Fraldinha\",\"quantidade\":1,\"preco_unitario\":\"58.00\"}]', '2025-07-02 11:11:05'),
(14, 8, 'ho501f6fn2dx5udz', 'pay_valz9wdtudczqwdl', 45.70, 'DELETED', 'https://sandbox.asaas.com/c/ho501f6fn2dx5udz', '[{\"nome\":\"Costela Bovina em Tira\",\"quantidade\":1,\"preco_unitario\":\"45.70\"}]', '2025-07-02 11:18:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `descricao` text NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `imagem` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `categoria`, `descricao`, `preco`, `imagem`) VALUES
(5, 'Picanha Premium', 'Bovino', 'Peça de picanha selecionada, ideal para churrasco.', 89.90, 'imagens/picanha.jpg'),
(6, 'Contrafilé (Peça)', 'Bovino', 'Capa de gordura uniforme, carne macia e suculenta.', 65.50, 'imagens/contrafile.jpg'),
(7, 'Fraldinha', 'Bovino', 'Corte com fibras longas, ideal para assados e grelhados.', 58.00, 'imagens/fraldinha.jpg'),
(8, 'Maminha', 'Bovino', 'Carne muito macia e suculenta, perfeita para o dia a dia.', 62.70, 'imagens/maminha.jpg'),
(9, 'Costela Bovina em Tira', 'Bovino', 'Perfeita para cozidos longos e churrasco de bafo.', 45.70, 'imagens/costela.jpg'),
(10, 'Linguiça Toscana Apimentada', 'Suíno', 'Linguiça de porco fresca com um toque de pimenta.', 32.50, 'imagens/linguica.jpg'),
(11, 'Costelinha Suína BBQ', 'Suíno', 'Já temperada e pronta para ir ao forno ou churrasqueira.', 49.90, 'imagens/costelinha_suina.jpg'),
(12, 'Lombo Suíno Fatiado', 'Suíno', 'Fatias prontas para grelhar, carne magra e saborosa.', 38.00, 'imagens/lombo.jpg'),
(13, 'Pancetta (Barriga de Porco)', 'Suíno', 'Ideal para pururuca, torresmo ou assados lentos.', 35.20, 'imagens/pancetta.jpg'),
(14, 'Bisteca Suína', 'Suíno', 'Corte clássico e versátil para fritar ou grelhar.', 29.80, 'imagens/bisteca.jpg'),
(15, 'Asa de Frango Temperada', 'Aves', 'Asinhas de frango com tempero especial da casa.', 22.00, 'imagens/asa_frango.jpg'),
(16, 'Coração de Frango no Espeto', 'Aves', 'Espetos prontos para o seu churrasco, um clássico.', 25.50, 'imagens/coracao_frango.jpg'),
(17, 'Filé de Peito de Frango', 'Aves', 'Peito de frango sem osso e sem pele, muito saudável.', 24.80, 'imagens/file_frango.jpg'),
(18, 'Coxinha da Asa (Drumette)', 'Aves', 'A parte mais carnuda da asa, ótima para fritar.', 26.90, 'imagens/coxinha_asa.jpg'),
(19, 'Sobrecoxa Desossada', 'Aves', 'Suculenta e sem ossos, perfeita para assar ou grelhar.', 28.50, 'imagens/sobrecoxa.jpg');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `cpf`, `senha`) VALUES
(8, 'Lian', 'lian@gmail.com', '52428393847', '$2y$10$gLaPMtfdYjDgIOgCyrEpyuC2Xm5lI7U8Mb3VndpYGVj0PQGreRWx6'),
(9, 'Gaginho2002', 'gaginho@gmail.com', '49252521860', '$2y$10$8B56vZ4khO7YiC36PYdOteYRbUImNAB/Vzq4Le8grbiG0zbbPeJlu');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
