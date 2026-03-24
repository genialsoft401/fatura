-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16-Dez-2025 às 15:32
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u523793545_sistema_fatura`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `issue_date` date NOT NULL,
  `due_date` int(11) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `observation` text DEFAULT NULL,
  `series` varchar(50) DEFAULT NULL,
  `retention` double DEFAULT NULL,
  `retention_value` double NOT NULL,
  `currency` varchar(10) NOT NULL,
  `manual_exchange_rate` double NOT NULL,
  `total_sum` double NOT NULL,
  `total_discount` double NOT NULL,
  `subtotal_without_tax` double NOT NULL,
  `total_tax` double NOT NULL,
  `final_total` double NOT NULL,
  `converted_total` double NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `paid_total` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `invoices`
--

INSERT INTO `invoices` (`id`, `contact_id`, `company_id`, `status`, `issue_date`, `due_date`, `reference`, `observation`, `series`, `retention`, `retention_value`, `currency`, `manual_exchange_rate`, `total_sum`, `total_discount`, `subtotal_without_tax`, `total_tax`, `final_total`, `converted_total`, `user_id`, `created_at`, `paid_total`) VALUES
(36, 9, 1, 1, '2025-02-16', 32, '0004', 'What is Lorem Ipsum?\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\r\n\r\nWhy do we use it?\r\nIt is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).\r\n\r\n', '2025', 3.11, 1395.16, 'NGN', 1.62, 46100, 1239.6, 44860.4, 3696, 47161.24, 76401.209, 3, '2025-02-16 13:07:31', 0),
(37, 11, 1, 1, '2025-02-16', 15, '', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\r\n\r\nWhy do we use it?\r\nIt is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).\r\n\r\n\r\nWhere does it come from?\r\nContrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.\r\n\r\nThe standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from \"de Finibus Bonorum et Malorum\" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.\r\n\r\nWhere can I get some?\r\nThere are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.\r\n\r\n5\r\n	paragraphs\r\n	words\r\n	bytes\r\n	lists\r\n	Start with \'Lorem\r\nipsum dolor sit amet...\'\r\n', '2025', 0, 0, 'AOA', 0, 1, 0, 1, 140, 1140, 0, 1, '2025-02-16 23:23:17', 0),
(38, 13, 1, 1, '2025-02-16', 15, '0005', '', '2025', 0, 0, 'AOA', 0, 120.1, 0, 120.1, 14, 120114, 0, 1, '2025-02-16 23:24:30', 0),
(39, 12, 1, 1, '2025-03-11', 15, '', 'teste', '2025', 0, 0, 'AOA', 0, 99.44, 0, 99.44, 13.921, 113361.6, 0, 3, '2025-03-11 17:42:06', 0),
(40, 9, 1, 1, '2025-03-24', 15, '', '', '2025', 0, 0, 'CAD', 0, 125, 50, 75, 0, 75, 0, 1, '2025-03-24 17:19:20', 0),
(41, 11, 1, 3, '2025-04-25', 30, '', '', '2025', 0, 0, 'HUF', 0, 10.25, 0, 10.25, 1.4, 11650, 0, 1, '2025-04-25 14:54:29', 0),
(42, 18, 2, 1, '2025-07-16', 15, '', '', '2025', 0, 0, 'AOA', 0, 210, 0, 210, 29.4, 239.4, 0, 3, '2025-07-16 12:49:50', 0),
(43, 17, 2, 5, '2025-07-16', 15, '', '', '2025', 0, 0, 'AOA', 0, 210, 0, 210, 29.4, 239.4, 0, 3, '2025-07-16 12:50:58', 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoices_ibfk_1` (`company_id`),
  ADD KEY `invoices_ibfk_2` (`contact_id`),
  ADD KEY `currency` (`currency`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `invoices_ibfk_5` (`status`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `contact` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `currencies` (`iso_code`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_ibfk_5` FOREIGN KEY (`status`) REFERENCES `invoice_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- --------------------------------------------------------

--
-- Estrutura da tabela `invoice_status`
--

CREATE TABLE `invoice_status` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(7) NOT NULL,
  `text_color` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `invoice_status`
--

INSERT INTO `invoice_status` (`id`, `name`, `color`, `text_color`) VALUES
(1, 'Rascunho', '#ff9933', '#ffffff'),
(2, 'Cancelado', '#a80000', '#ffffff'),
(3, 'Finalizado', '#003a8c', '#ffffff'),
(4, 'Pago', '#207d01', '#ffffff'),
(5, 'Parcial', '#efb100', '#fff');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `invoice_status`
--
ALTER TABLE `invoice_status`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `invoice_status`
--
ALTER TABLE `invoice_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
