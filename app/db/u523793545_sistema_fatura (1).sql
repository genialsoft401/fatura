-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13/05/2025 às 16:51
-- Versão do servidor: 10.11.10-MariaDB
-- Versão do PHP: 7.2.34

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
CREATE DATABASE IF NOT EXISTS `u523793545_sistema_fatura` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `u523793545_sistema_fatura`;


GRANT ALL PRIVILEGES ON *.* TO `u523793545_sistema`@`%` IDENTIFIED BY PASSWORD '*85373E75D616722E5D67B72E8215837402525965' WITH GRANT OPTION;
-- --------------------------------------------------------

--
-- Estrutura para tabela `companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `acronym` varchar(6) DEFAULT NULL,
  `registration_number` varchar(50) NOT NULL,
  `phone_ddi` varchar(5) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `currency` varchar(3) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `blocked` tinyint(1) NOT NULL,
  `update_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `companies`
--

INSERT INTO `companies` (`id`, `name`, `acronym`, `registration_number`, `phone_ddi`, `phone`, `email`, `website`, `address`, `city`, `country`, `zip_code`, `logo_url`, `currency`, `is_active`, `blocked`, `update_at`, `created_at`) VALUES
(1, 'WPERSON Consultoria', 'WP-CP', '123456789', '244', '921 829 100', 'atendimento@wperson.com', 'https://wperson.com', 'WPERSON. Angola Rua Abdel Nasser, Edifício Loanda Towers, Piso 09, Ingombota', 'Luanda', 'Angola', '10001', 'logo-wperson.png', 'AOA', 1, 1, '2025-04-11 13:31:43', '2025-02-07 17:15:55'),
(2, 'Global Innovations', 'GINN', '987654321', '244', '987-654-3210', 'info@globalinnovations.com', 'https://globalinnovations.com', '456 Innovation Ave', 'Luanda', 'Angola', '94105', 'global.png', 'AOA', 0, 1, '2025-04-11 13:03:17', '2025-02-07 17:15:55');

-- --------------------------------------------------------

--
-- Estrutura para tabela `company_has_user`
--

DROP TABLE IF EXISTS `company_has_user`;
CREATE TABLE `company_has_user` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('owner','admin','employee','viewer') DEFAULT 'employee',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `company_has_user`
--

INSERT INTO `company_has_user` (`id`, `company_id`, `user_id`, `role`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'owner', '2025-01-29 12:29:17', '2025-01-29 11:58:23'),
(2, 2, 4, 'employee', '2025-01-30 11:45:39', '2025-01-30 10:45:39'),
(3, 2, 3, 'admin', '2025-01-31 18:04:34', '2025-01-31 18:04:34'),
(4, 1, 3, 'owner', '2025-02-03 02:04:52', '2025-02-03 02:04:52'),
(5, 2, 1, 'owner', '2025-02-09 12:52:18', '2025-02-09 12:52:18'),
(6, 1, 5, 'owner', '2025-02-11 13:31:31', '2025-02-11 13:31:31'),
(7, 2, 5, 'owner', '2025-02-11 13:31:41', '2025-02-11 13:31:47'),
(8, 1, 7, 'admin', '2025-02-11 13:49:53', '2025-02-11 13:49:53');

-- --------------------------------------------------------

--
-- Estrutura para tabela `contact`
--

DROP TABLE IF EXISTS `contact`;
CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contributor` varchar(100) DEFAULT NULL,
  `address` tinytext NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Angola',
  `city` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telephone_ddi` varchar(5) NOT NULL,
  `telephone` varchar(60) DEFAULT NULL,
  `cellphone_ddi` varchar(10) DEFAULT NULL,
  `cellphone` varchar(50) DEFAULT NULL,
  `po_box` varchar(255) NOT NULL,
  `fax` varchar(50) DEFAULT NULL,
  `pref_name` varchar(255) DEFAULT NULL,
  `pref_email` varchar(150) DEFAULT NULL,
  `pref_telephone_ddi` varchar(5) DEFAULT NULL,
  `pref_telephone` varchar(60) DEFAULT NULL,
  `pref_cellphone_ddi` varchar(5) DEFAULT NULL,
  `pref_cellphone` varchar(60) DEFAULT NULL,
  `numberCopys` int(11) NOT NULL,
  `observations` text DEFAULT NULL,
  `due_date` int(11) NOT NULL,
  `language` varchar(3) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `currency` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `contact`
--

INSERT INTO `contact` (`id`, `company_id`, `type`, `name`, `contributor`, `address`, `website`, `country`, `city`, `email`, `telephone_ddi`, `telephone`, `cellphone_ddi`, `cellphone`, `po_box`, `fax`, `pref_name`, `pref_email`, `pref_telephone_ddi`, `pref_telephone`, `pref_cellphone_ddi`, `pref_cellphone`, `numberCopys`, `observations`, `due_date`, `language`, `payment_method`, `currency`, `created_at`, `updated_at`) VALUES
(9, 1, 'Autofacturação', 'Empresa X', '1231', 'Rua A, 1232', 'https://empresa1.com', 'Angola', 'Luanda', 'carlos.silva@email.com', '244', '912345678', '244', '923456789', '10012', '222-3333', 'Carlos Silva', 'carlos.silva@empresa1.com', '244', '912345678', '244', '923456789', 2, 'Cliente VIP', 30, 'AO', 'dinheiro', 'AOA', '2025-02-05 00:35:27', '2025-02-07 17:38:45'),
(11, 1, 'Normal', 'XYZ Corp', '3636356', 'Rua Nova, 789', 'https://parceiro1.com', 'Angola', 'Huambo', 'lucas.ferreira@email.com', '244', '934567890', '244', '945678901', '1003', '444-5555', 'Lucas Ferreira', 'lucas.ferreira@parceiro1.com', '244', '934567890', '244', '945678901', 3, 'Contrato renovado', 10, 'AO', 'cartão', 'AOA', '2025-02-05 00:35:27', '2025-02-07 17:38:48'),
(12, 1, 'Normal', 'Distribuidora ABC', '63573474', 'Rua das Flores, 321', 'https://distribuidora1.com', 'Angola', 'Lubango', 'mariana.santos@email.com', '244', '945678901', '244', '956789012', '1004', '555-6666', 'Mariana Santos', 'mariana.santos@distribuidora1.com', '244', '945678901', '244', '956789012', 2, 'Entrega semanal', 15, 'AO', 'boleto', 'AOA', '2025-02-05 00:35:27', '2025-02-07 17:38:52'),
(13, 1, 'Normal', 'MegaCorp', '75634', 'Rua Principal, 567', 'https://megacorp.com', 'Angola', 'Namibe', 'fernando.oliveira@email.com', '244', '956789012', '244', '967890123', '1005', '666-7777', 'Fernando Oliveira', 'fernando.oliveira@megacorp.com', '244', '956789012', '244', '967890123', 1, 'Preferência por contato telefônico', 30, 'AO', 'PIX', 'AOA', '2025-02-05 00:35:27', '2025-02-07 17:38:54'),
(14, 2, 'Autofacturação', 'TEEech Solutions', '755485663', 'Avenida Nova, 654', 'https://techsolutions.com', 'Angola', 'Saurimo', 'bruno.almeida@email.com', '244', '967890123', '244', '978901234', '2001', '777-8888', 'Bruno Almeida', 'bruno.almeida@techsolutions.com', '244', '967890123', '244', '978901234', 2, 'Cliente recorrente', 30, 'AO', '', 'AOA', '2025-02-05 00:35:27', '2025-02-07 17:39:40'),
(15, 2, 'Autofacturação', 'Rocha & Cia', '2343534', 'Rua do Comércio, 987', 'https://rochaecia.com', 'Angola', 'Uíge', 'tatiane.rocha@email.com', '244', '978901234', '244', '989012345', '2002', '888-9999', 'Tatiane Rocha', 'tatiane.rocha@rochaecia.com', '244', '978901234', '244', '989012345', 1, 'Fornecimento mensal', 20, 'AO', 'dinheiro', 'AOA', '2025-02-05 00:35:27', '2025-02-07 17:39:00'),
(16, 2, 'Autofacturação', 'Mendes Ltda', '412312', 'Rua Industrial, 852', 'https://mendesltda.com', 'Angola', 'Caxito', 'rodrigo.mendes@email.com', '244', '989012345', '244', '990123456', '2003', '999-0000', 'Rodrigo Mendes', 'rodrigo.mendes@mendesltda.com', '244', '989012345', '244', '990123456', 3, 'Projeto conjunto em andamento', 10, 'AO', 'boleto', 'AOA', '2025-02-05 00:35:27', '2025-02-07 17:39:03'),
(17, 2, 'Normal', 'Nunes Distribuidora', '132534', 'Avenida Sul, 753', 'https://nunesdistribuidora.com', 'Angola', 'Malanje', 'gabriela.nunes@email.com', '244', '990123456', '244', '901234567', '2004', '000-1111', 'Gabriela Nunes', 'gabriela.nunes@nunesdistribuidora.com', '244', '990123456', '244', '901234567', 2, 'Entrega quinzenal', 15, 'AO', 'cartão', 'AOA', '2025-02-05 00:35:27', '2025-02-07 17:39:07'),
(18, 2, 'Normal', 'Pereira Tech', '452344', 'Rua Tecnológica, 159', 'https://pereiratech.com', 'Angola', 'Menongue', 'ricardo.pereira@email.com', '244', '901234567', '244', '912345678', '2005', '111-2222', 'Ricardo Pereira', 'ricardo.pereira@pereiratech.com', '244', '901234567', '244', '912345678', 1, 'Prefere comunicação via e-mail', 30, 'AO', 'PIX', 'AOA', '2025-02-05 00:35:27', '2025-02-07 17:39:09'),
(19, 2, 'Autofacturação', 'Empresa X', '35142543', 'Rua A, 123', 'https://empresa1.com', 'Angola', 'Luanda', 'carlos.silva@email.com', '244', '912345678', '244', '', '1001', '222-3333', 'Carlos Silva', 'carlos.silva@empresa1.com', '244', '912345678', '244', '923456789', 1, NULL, 30, 'AO', 'dinheiro', 'AOA', '2025-02-07 11:25:01', '2025-02-07 17:39:11'),
(25, 0, '', '', NULL, '', NULL, 'Angola', '', '', '', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, '', '2025-02-08 14:33:26', '2025-02-08 14:33:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `countries`
--

DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `code` varchar(10) NOT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `iso` varchar(5) DEFAULT NULL,
  `iso3` varchar(5) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `formal_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `countries`
--

INSERT INTO `countries` (`code`, `phone`, `iso`, `iso3`, `name`, `formal_name`) VALUES
('10', '672', 'AQ', 'ATA', 'Antártida', 'Antártida'),
('100', '359', 'BG', 'BGR', 'Bulgária', 'República da Bulgária'),
('104', '95', 'MM', 'MMR', 'Birmânia', 'República da União de Myanmar'),
('108', '257', 'BI', 'BDI', 'Burundi', 'República do Burundi'),
('112', '375', 'BY', 'BLR', 'Bielorrússia', 'República da Bielorrússia'),
('116', '855', 'KH', 'KHM', 'Camboja', 'Reino do Camboja'),
('12', '213', 'DZ', 'DZA', 'Algéria', 'República Democrática Popular da Algéria'),
('120', '237', 'CM', 'CMR', 'Camarões', 'República de Camarões'),
('124', '1', 'CA', 'CAN', 'Canadá', 'Canadá'),
('132', '238', 'CV', 'CPV', 'Cabo Verde', 'República do Cabo Verde'),
('136', '1345', 'KY', 'CYM', 'Ilhas Cayman', 'Ilhas Cayman'),
('140', '236', 'CF', 'CAF', 'República Centro-Africana', 'República Centro-Africana'),
('144', '94', 'LK', 'LKA', 'Sri Lanka', 'República Democrática Socialista do Sri Lanka'),
('148', '235', 'TD', 'TCD', 'Chade', 'República do Chade'),
('152', '56', 'CL', 'CHL', 'Chile', 'República do Chile'),
('156', '86', 'CN', 'CHN', 'China', 'República Popular da China'),
('158', '886', 'TW', 'TWN', 'Taiwan', 'Taiwan'),
('16', '1684', 'AS', 'ASM', 'Samoa Americana', 'Território de Samoa Americana'),
('162', '61', 'CX', 'CXR', 'Ilha Christmas', 'Território da Ilha Christmas'),
('166', '672', 'CC', 'CCK', 'Ilhas Cocos (Keeling)', 'Território das Ilhas Cocos (Keeling)'),
('170', '57', 'CO', 'COL', 'Colômbia', 'República da Colômbia'),
('174', '269', 'KM', 'COM', 'Comores', 'União das Comores'),
('175', '269', 'YT', 'MYT', 'Mayotte', 'Departamento de Mayotte'),
('178', '242', 'CG', 'COG', 'Congo', 'República do Congo'),
('180', '242', 'CD', 'COD', 'Congo (DR)', 'República Democrática do Congo'),
('184', '682', 'CK', 'COK', 'Ilhas Cook', 'Ilhas Cook'),
('188', '506', 'CR', 'CRI', 'Costa Rica', 'República da Costa Rica'),
('191', '385', 'HR', 'HRV', 'Croácia', 'República da Croácia'),
('192', '53', 'CU', 'CUB', 'Cuba', 'República de Cuba'),
('196', '357', 'CY', 'CYP', 'Chipre', 'República do Chipre'),
('20', '376', 'AD', 'AND', 'Andorra', 'Principado Andorra'),
('203', '420', 'CZ', 'CZE', 'República Tcheca', 'República Tcheca'),
('204', '229', 'BJ', 'BEN', 'Benin', 'República do Benin'),
('208', '45', 'DK', 'DNK', 'Dinamarca', 'Reino da Dinamarca'),
('212', '1767', 'DM', 'DMA', 'Dominica', 'Comunidade da Dominica'),
('214', '1809', 'DO', 'DOM', 'República Dominicana', 'República Dominicana'),
('218', '593', 'EC', 'ECU', 'Equador', 'República do Equador'),
('222', '503', 'SV', 'SLV', 'El Salvador', 'República El Salvador'),
('226', '240', 'GQ', 'GNQ', 'Guiné Equatorial', 'República do Guiné Equatorial'),
('231', '251', 'ET', 'ETH', 'Etiópia', 'República Democrática Federal da Etiópia'),
('232', '291', 'ER', 'ERI', 'Eritreia', 'Estado da Eritreia'),
('233', '372', 'EE', 'EST', 'Estônia', 'República da Estônia'),
('234', '298', 'FO', 'FRO', 'Ilhas Faroe', 'Ilhas Faroe'),
('238', '500', 'FK', 'FLK', 'Ilhas Malvinas', 'Ilhas Malvinas'),
('239', '500', 'GS', 'SGS', 'Ilhas Geórgia do Sul e Sandwich do Sul', 'Ilhas Geórgia do Sul e Sandwich do Sul'),
('24', '244', 'AO', 'AGO', 'Angola', 'República de Angola'),
('242', '679', 'FJ', 'FJI', 'Fiji', 'República do Fiji'),
('246', '358', 'FI', 'FIN', 'Finlândia', 'República da Finlândia'),
('250', '33', 'FR', 'FRA', 'França', 'República Francesa'),
('254', '594', 'GF', 'GUF', 'Guiana Francesa', 'Guiana Francesa'),
('258', '689', 'PF', 'PYF', 'Polinésia Francesa', 'Polinésia Francesa'),
('260', '33', 'TF', 'ATF', 'Terras Austrais e Antárticas Francesas', 'Território das Terras Austrais e Antárticas Francesas'),
('262', '253', 'DJ', 'DJI', 'Djibuti', 'República do Djibuti'),
('266', '241', 'GA', 'GAB', 'Gabão', 'República Gabonesa'),
('268', '995', 'GE', 'GEO', 'Geórgia', 'Geórgia'),
('270', '220', 'GM', 'GMB', 'Gâmbia', 'República da Gâmbia'),
('275', '970', 'PS', 'PSE', 'Palestina', 'Estado da Palestina'),
('276', '49', 'DE', 'DEU', 'Alemanha', 'República Federal da Alemanha'),
('28', '1268', 'AG', 'ATG', 'Antigua e Barbuda', 'Antigua e Barbuda'),
('288', '233', 'GH', 'GHA', 'Gana', 'Repúblia de Gana'),
('292', '350', 'GI', 'GIB', 'Gibraltar', 'Gibraltar'),
('296', '686', 'KI', 'KIR', 'Kiribati', 'República do Kiribati'),
('300', '30', 'GR', 'GRC', 'Grécia', 'República Helênica'),
('304', '299', 'GL', 'GRL', 'Groelândia', 'Groelândia'),
('308', '1473', 'GD', 'GRD', 'Granada', 'Granada'),
('31', '994', 'AZ', 'AZE', 'Azerbaijão', 'República do Azerbaijão'),
('312', '590', 'GP', 'GLP', 'Guadalupe', 'Guadalupe'),
('316', '1671', 'GU', 'GUM', 'Guão', 'Território do Guão'),
('32', '54', 'AR', 'ARG', 'Argentina', 'República Argentina'),
('320', '502', 'GT', 'GTM', 'Guatemala', 'República da Guatemala'),
('324', '224', 'GN', 'GIN', 'Guiné', 'República do Guiné'),
('328', '592', 'GY', 'GUY', 'Guiana', 'República Cooperativa da Guiana'),
('332', '509', 'HT', 'HTI', 'Haiti', 'República do Haiti'),
('334', '672', 'HM', 'HMD', 'Ilhas Heard e McDonald', 'Território das Ilhas Heard e McDonald'),
('336', '39', 'VA', 'VAT', 'Vaticano', 'Estado da Cidade do Vaticano'),
('340', '504', 'HN', 'HND', 'Honduras', 'República de Honduras'),
('344', '852', 'HK', 'HKG', 'Hong Kong', 'Região Administrativa Especial de Hong Kong da República Popular da China'),
('348', '36', 'HU', 'HUN', 'Hungria', 'Hungria'),
('352', '354', 'IS', 'ISL', 'Islândia', 'Islândia'),
('356', '91', 'IN', 'IND', 'Índia', 'República da Índia'),
('36', '61', 'AU', 'AUS', 'Austrália', 'Comunidade da Austrália'),
('360', '62', 'ID', 'IDN', 'Indonésia', 'República da Indonésia'),
('364', '98', 'IR', 'IRN', 'Iran', 'República Islâmica do Iran'),
('368', '964', 'IQ', 'IRQ', 'Iraque', 'República do Iraque'),
('372', '353', 'IE', 'IRL', 'Irlanda', 'Irlanda'),
('376', '972', 'IL', 'ISR', 'Israel', 'Estado de Israel'),
('380', '39', 'IT', 'ITA', 'Itália', 'República Italiana'),
('384', '225', 'CI', 'CIV', 'Costa do Marfim', 'República da Costa do Marfim'),
('388', '1876', 'JM', 'JAM', 'Jamaica', 'Jamaica'),
('392', '81', 'JP', 'JPN', 'Japão', 'Japão'),
('398', '7', 'KZ', 'KAZ', 'Cazaquistão', 'República do Cazaquistão'),
('4', '93', 'AF', 'AFG', 'Afeganistão', 'República Islâmica do Afeganistão'),
('40', '43', 'AT', 'AUT', 'Áustria', 'República da Áustria'),
('400', '962', 'JO', 'JOR', 'Jordânia', 'Reino Hachemita da Jordânia'),
('404', '254', 'KE', 'KEN', 'Quênia', 'República do Quênia'),
('408', '850', 'KP', 'PRK', 'Coreia do Norte', 'República Democrática Popular da Coreia'),
('410', '82', 'KR', 'KOR', 'Coreia do Sul', 'República da Coreia'),
('414', '965', 'KW', 'KWT', 'Kuwait', 'Estado do Kuwait'),
('417', '996', 'KG', 'KGZ', 'Quirguistão', 'República Quirguiz'),
('418', '856', 'LA', 'LAO', 'Laos', 'República Democrática Popular Lau'),
('422', '961', 'LB', 'LBN', 'Líbano', 'República Libanesa'),
('426', '266', 'LS', 'LSO', 'Lesoto', 'Reino do Lesoto'),
('428', '371', 'LV', 'LVA', 'Letônia', 'República da Letônia'),
('430', '231', 'LR', 'LBR', 'Libéria', 'República da Libéria'),
('434', '218', 'LY', 'LBY', 'Líbia', 'Líbia'),
('438', '423', 'LI', 'LIE', 'Liechtenstein', 'Principado de Liechtenstein'),
('44', '1242', 'BS', 'BHS', 'Bahamas', 'Comunidade de Bahamas'),
('440', '370', 'LT', 'LTU', 'Lituânia', 'República da Lituânia'),
('442', '352', 'LU', 'LUX', 'Luxemburgo', 'Grão-Ducado do Luxemburgo'),
('446', '853', 'MO', 'MAC', 'Macao', 'Macao'),
('450', '261', 'MG', 'MDG', 'Madagascar', 'República de Madagascar'),
('454', '265', 'MW', 'MWI', 'Malawi', 'República de Malawi'),
('458', '60', 'MY', 'MYS', 'Malásia', 'Malásia'),
('462', '960', 'MV', 'MDV', 'Maldivas', 'Reública de Maldivas'),
('466', '223', 'ML', 'MLI', 'Mali', 'República do Mali'),
('470', '356', 'MT', 'MLT', 'Malta', 'República de Malta'),
('474', '596', 'MQ', 'MTQ', 'Martinica', 'Martinica'),
('478', '222', 'MR', 'MRT', 'Mauritânia', 'República Islâmica da Mauritânia'),
('48', '973', 'BH', 'BHR', 'Bahrein', 'Reino do Bahrein'),
('480', '230', 'MU', 'MUS', 'Maurício', 'República de Maurício'),
('484', '52', 'MX', 'MEX', 'México', 'Estados Unidos Mexicanos'),
('492', '377', 'MC', 'MCO', 'Mônaco', 'Principado de Mônaco'),
('496', '976', 'MN', 'MNG', 'Mongólia', 'Mongólia'),
('498', '373', 'MD', 'MDA', 'Moldova', 'República de Moldova'),
('50', '880', 'BD', 'BGD', 'Bangladesh', 'República Popular de Bangladesh'),
('500', '1664', 'MS', 'MSR', 'Montserrat', 'Montserrat'),
('504', '212', 'MA', 'MAR', 'Marrocos', 'Reino de Marrocos'),
('508', '258', 'MZ', 'MOZ', 'Moçambique', 'República de Moçambique'),
('51', '374', 'AM', 'ARM', 'Armênia', 'República da Armênia'),
('512', '968', 'OM', 'OMN', 'Omã', 'Sultanato de Omã'),
('516', '264', 'NA', 'NAM', 'Namíbia', 'República da Namíbia'),
('52', '246', 'BB', 'BRB', 'Barbados', 'Barbados'),
('520', '674', 'NR', 'NRU', 'Nauru', 'República de Nauru'),
('524', '977', 'NP', 'NPL', 'Nepal', 'República Democrática Federativa do Nepal'),
('528', '31', 'NL', 'NLD', 'Holanda', 'Holanda'),
('530', '599', 'AN', 'ANT', 'Antilhas Holandesas', 'Antilhas Holandesas'),
('533', '297', 'AW', 'ABW', 'Aruba', 'Aruba'),
('540', '687', 'NC', 'NCL', 'Nova Caledônia', 'Nova Caledônia'),
('548', '678', 'VU', 'VUT', 'Vanuatu', 'República de Vanuatu'),
('554', '64', 'NZ', 'NZL', 'Nova Zelândia', 'Nova Zelândia'),
('558', '505', 'NI', 'NIC', 'Nicarágua', 'República da Nicarágua'),
('56', '32', 'BE', 'BEL', 'Bélgica', 'Reino da Bélgica'),
('562', '227', 'NE', 'NER', 'Niger', 'República do Niger'),
('566', '234', 'NG', 'NGA', 'Nigéria', 'República Federativa da Nigéria'),
('570', '683', 'NU', 'NIU', 'Niue', 'Niue'),
('574', '672', 'NF', 'NFK', 'Ilha Norfolk', 'Território da Ilha Norfolk'),
('578', '47', 'NO', 'NOR', 'Noruega', 'Reino da Noruega'),
('580', '1670', 'MP', 'MNP', 'Ilhas Marianas do Norte', 'Comunidade das Ilhas Marianas do Norte'),
('581', '1', 'UM', 'UMI', 'Ilhas Menores Distantes dos Estados Unidos', 'Ilhas Menores Distantes dos Estados Unidos'),
('583', '691', 'FM', 'FSM', 'Micronésia', 'Estados Federados da Micronesia'),
('584', '692', 'MH', 'MHL', 'Ilhas Marshall', 'República das Ilhas Marshall'),
('585', '680', 'PW', 'PLW', 'Palau', 'República de Palau'),
('586', '92', 'PK', 'PAK', 'Paquistão', 'República Islâmica do Paquistão'),
('591', '507', 'PA', 'PAN', 'Panamá', 'República do Panamá'),
('598', '675', 'PG', 'PNG', 'Papua-Nova Guiné', 'Estado Independente da Papua-Nova Guiné'),
('60', '1441', 'BM', 'BMU', 'Bermuda', 'Bermuda'),
('600', '595', 'PY', 'PRY', 'Paraguai', 'República do Paraguai'),
('604', '51', 'PE', 'PER', 'Peru', 'República do Peru'),
('608', '63', 'PH', 'PHL', 'Filipinas', 'República das Filipinas'),
('612', '672', 'PN', 'PCN', 'Ilhas Picárnia', 'Ilhas Picárnia'),
('616', '48', 'PL', 'POL', 'Polônia', 'República da Polônia'),
('620', '351', 'PT', 'PRT', 'Portugal', 'República Portuguesa'),
('624', '245', 'GW', 'GNB', 'Guiné-Bissau', 'República da Guiné-Bissau'),
('626', '670', 'TL', 'TLS', 'Timor-Leste', 'República Democrática de Timor-Leste'),
('630', '1787', 'PR', 'PRI', 'Porto Rico', 'Comunidade do Porto Rico'),
('634', '974', 'QA', 'QAT', 'Catar', 'Estado do Catar'),
('638', '262', 'RE', 'REU', 'Reunião', 'Polônia'),
('64', '975', 'BT', 'BTN', 'Butão', 'Reino do Butão'),
('642', '40', 'RO', 'ROM', 'Romênia', 'Romênia'),
('643', '70', 'RU', 'RUS', 'Rússia', 'Federação Russa'),
('646', '250', 'RW', 'RWA', 'Ruanda', 'República da Ruanda'),
('654', '290', 'SH', 'SHN', 'Santa Helena', 'Saint Helena'),
('659', '1869', 'KN', 'KNA', 'São Cristóvão', 'São Cristóvão'),
('660', '1264', 'AI', 'AIA', 'Anguilla', 'Anguilla'),
('662', '1758', 'LC', 'LCA', 'Santa Lúcia', 'Santa Lúcia'),
('666', '508', 'PM', 'SPM', 'São Pedro e Miquelon', 'Coletividade Territorial de São Pedro e Miquelon'),
('670', '1784', 'VC', 'VCT', 'São Vicente e Granadinas', 'São Vicente e Granadinas'),
('674', '378', 'SM', 'SMR', 'São Marino', 'República de São Marino'),
('678', '239', 'ST', 'STP', 'Sao Tomé e Príncipe', 'República Democrática de Sao Tomé e Príncipe'),
('68', '591', 'BO', 'BOL', 'Bolívia', 'Estado Plurinacional da Bolívia'),
('682', '966', 'SA', 'SAU', 'Arábia Saudita', 'Reino da Arábia Saudita'),
('686', '221', 'SN', 'SEN', 'Senegal', 'República do Senegal'),
('688', '381', 'CS', 'SRB', 'Sérvia e Montenegro', 'União Estatal de Sérvia e Montenegro'),
('690', '248', 'SC', 'SYC', 'Seicheles', 'República das Seicheles'),
('694', '232', 'SL', 'SLE', 'República da Serra Leoa', 'República da Serra Leoa'),
('70', '387', 'BA', 'BIH', 'Bósnia e Herzegovina', 'Bósnia e Herzegovina'),
('702', '65', 'SG', 'SGP', 'Singapura', 'República da Singapura'),
('703', '421', 'SK', 'SVK', 'Eslováquia', 'República Eslovaca'),
('704', '84', 'VN', 'VNM', 'Vietnam', 'República Socialista do Vietnam'),
('705', '386', 'SI', 'SVN', 'Eslovênia', 'República da Eslovênia'),
('706', '252', 'SO', 'SOM', 'Somália', 'República da Somália'),
('710', '27', 'ZA', 'ZAF', 'África do Sul', 'República da África do Sul'),
('716', '263', 'ZW', 'ZWE', 'Zimbábue', 'República do Zimbábue'),
('72', '267', 'BW', 'BWA', 'Botswana', 'República da Botswana'),
('724', '34', 'ES', 'ESP', 'Espanha', 'Reino da Espanha'),
('732', '212', 'EH', 'ESH', 'Saara Ocidental', 'Saara Ocidental'),
('736', '249', 'SD', 'SDN', 'Sudão', 'República do Sudão'),
('74', '47', 'BV', 'BVT', 'Ilha Bouvet', 'Ilha Bouvet'),
('740', '597', 'SR', 'SUR', 'Suriname', 'República do Suriname'),
('744', '47', 'SJ', 'SJM', 'Esvalbarde', 'Esvalbarde'),
('748', '268', 'SZ', 'SWZ', 'Suazilândia', 'Reino da Suazilândia'),
('752', '46', 'SE', 'SWE', 'Suécia', 'Reino da Suécia'),
('756', '41', 'CH', 'CHE', 'Suiça', 'Confederação Suiça'),
('76', '55', 'BR', 'BRA', 'Brasil', 'República Federativa do Brasil'),
('760', '963', 'SY', 'SYR', 'Síria', 'República Árabe Síria'),
('762', '992', 'TJ', 'TJK', 'Tajiquistão', 'República do Tajiquistão'),
('764', '66', 'TH', 'THA', 'Tailândia', 'Reino da Tailândia'),
('768', '228', 'TG', 'TGO', 'Togo', 'República Togolesa'),
('772', '690', 'TK', 'TKL', 'Toquelau', 'Toquelau'),
('776', '676', 'TO', 'TON', 'Tonga', 'Reino de Tonga'),
('780', '1868', 'TT', 'TTO', 'Trinidad e Tobago', 'República da Trinidad e Tobago'),
('784', '971', 'AE', 'ARE', 'Emirados Árabes', 'Emirados Árabes Unidos'),
('788', '216', 'TN', 'TUN', 'Tunísia', 'República da Tunísia'),
('792', '90', 'TR', 'TUR', 'Turquia', 'República da Turquia'),
('795', '7370', 'TM', 'TKM', 'Turcomenistão', 'Turcomenistão'),
('796', '1649', 'TC', 'TCA', 'Ilhas Turks e Caicos', 'Ilhas Turks e Caicos'),
('798', '688', 'TV', 'TUV', 'Tuvalu', 'Tuvalu'),
('8', '355', 'AL', 'ALB', 'Albânia', 'República da Albânia'),
('800', '256', 'UG', 'UGA', 'Uganda', 'República de Uganda'),
('804', '380', 'UA', 'UKR', 'Ucrânia', 'Ucrânia'),
('807', '389', 'MK', 'MKD', 'Macedônia', 'República da Macedônia'),
('818', '20', 'EG', 'EGY', 'Egito', 'República Árabe do Egito'),
('826', '44', 'GB', 'GBR', 'Reino Unido', 'Reino Unido da Grã-Bretanha e Irlanda do Norte'),
('834', '255', 'TZ', 'TZA', 'Tanzânia', 'República Unida da Tanzânia'),
('84', '501', 'BZ', 'BLZ', 'Belize', 'Belize'),
('840', '1', 'US', 'USA', 'Estados Unidos', 'Estados Unidos da América'),
('850', '1340', 'VI', 'VIR', 'Ilhas Virgens (USA)', 'Ilhas Virgens dos Estados Unidos'),
('854', '226', 'BF', 'BFA', 'Burkina Faso', 'Burkina Faso'),
('858', '598', 'UY', 'URY', 'Uruguai', 'República Oriental do Uruguai'),
('86', '246', 'IO', 'IOT', 'Território Britânico do Oceano Índico', 'Território Britânico do Oceano Índico'),
('860', '998', 'UZ', 'UZB', 'Uzbequistão', 'República do Uzbequistão'),
('862', '58', 'VE', 'VEN', 'Venezuela', 'República Bolivariana da Venezuela'),
('876', '681', 'WF', 'WLF', 'Wallis e Futuna', 'Wallis e Futuna'),
('882', '684', 'WS', 'WSM', 'Samoa', 'Estado Independente de Samoa'),
('887', '967', 'YE', 'YEM', 'Iêmen', 'República do Iêmen'),
('894', '260', 'ZM', 'ZMB', 'Zâmbia', 'República do Zâmbia'),
('90', '677', 'SB', 'SLB', 'Ilhas Salomão', 'Ilhas Salomão'),
('92', '1284', 'VG', 'VGB', 'Ilhas Virgens Inglesas', 'Ilhas Virgens'),
('96', '673', 'BN', 'BRN', 'Brunei', 'Estado do Brunei Darussalam');

-- --------------------------------------------------------

--
-- Estrutura para tabela `currencies`
--

DROP TABLE IF EXISTS `currencies`;
CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `iso_code` varchar(10) DEFAULT NULL,
  `currency` varchar(100) DEFAULT NULL,
  `symbol` varchar(10) DEFAULT NULL,
  `position` enum('left','right') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `currencies`
--

INSERT INTO `currencies` (`id`, `iso_code`, `currency`, `symbol`, `position`) VALUES
(1, 'AFN', 'Afegane Afegão', '؋', 'right'),
(2, 'ALL', 'Lek Albanês', 'L', 'right'),
(3, 'DZD', 'Dinar Argelino', 'د.ج', 'right'),
(4, 'AOA', 'Kwanza Angolano', 'Kz', 'right'),
(5, 'ARS', 'Peso Argentino', '$', 'left'),
(6, 'AUD', 'Dólar Australiano', '$', 'left'),
(7, 'AZN', 'Manat do Azerbaijão', '₼', 'right'),
(8, 'BHD', 'Dinar do Bahrein', '.د.ب', 'right'),
(9, 'BDT', 'Taka de Bangladesh', '৳', 'right'),
(10, 'BYN', 'Rublo Bielorrusso', 'Br', 'right'),
(11, 'BMD', 'Dólar das Bermudas', '$', 'left'),
(12, 'BRL', 'Real Brasileiro', 'R$', 'left'),
(13, 'CAD', 'Dólar Canadense', '$', 'left'),
(14, 'CLP', 'Peso Chileno', '$', 'left'),
(15, 'CNY', 'Yuan Chinês', '¥', 'left'),
(16, 'COP', 'Peso Colombiano', '$', 'left'),
(17, 'CRC', 'Colón Costarriquenho', '₡', 'left'),
(18, 'CUP', 'Peso Cubano', '$', 'left'),
(19, 'CZK', 'Coroa Tcheca', 'Kč', 'right'),
(20, 'DKK', 'Coroa Dinamarquesa', 'kr', 'right'),
(21, 'DOP', 'Peso Dominicano', '$', 'left'),
(22, 'EGP', 'Libra Egípcia', '£', 'left'),
(23, 'EUR', 'Euro', '€', 'left'),
(24, 'GEL', 'Lari Georgiano', '₾', 'right'),
(25, 'GHS', 'Cedi Ganês', '₵', 'right'),
(26, 'HKD', 'Dólar de Hong Kong', '$', 'left'),
(27, 'HUF', 'Forint Húngaro', 'Ft', 'right'),
(28, 'IDR', 'Rupia Indonésia', 'Rp', 'left'),
(29, 'ILS', 'Novo Shekel Israelense', '₪', 'left'),
(30, 'INR', 'Rupia Indiana', '₹', 'left'),
(31, 'IQD', 'Dinar Iraquiano', 'ع.د', 'right'),
(32, 'IRR', 'Rial Iraniano', '﷼', 'right'),
(33, 'ISK', 'Coroa Islandesa', 'kr', 'right'),
(34, 'JMD', 'Dólar Jamaicano', '$', 'left'),
(35, 'JPY', 'Iene Japonês', '¥', 'left'),
(36, 'KES', 'Xelim Queniano', 'KSh', 'right'),
(37, 'KGS', 'Som Quirguistanês', 'лв', 'right'),
(38, 'KHR', 'Riel Cambojano', '៛', 'right'),
(39, 'KRW', 'Won Sul-Coreano', '₩', 'left'),
(40, 'KWD', 'Dinar Kuwaitiano', 'د.ك', 'right'),
(41, 'KZT', 'Tenge Cazaque', '₸', 'right'),
(42, 'LBP', 'Libra Libanesa', 'ل.ل', 'right'),
(43, 'LKR', 'Rupia do Sri Lanka', 'Rs', 'right'),
(44, 'LYD', 'Dinar Líbio', 'ل.د', 'right'),
(45, 'MAD', 'Dirham Marroquino', 'د.م.', 'right'),
(46, 'MDL', 'Leu Moldávio', 'L', 'right'),
(47, 'MGA', 'Ariary Malgaxe', 'Ar', 'right'),
(48, 'MKD', 'Dinar Macedônio', 'ден', 'right'),
(49, 'MMK', 'Kyat de Mianmar', 'K', 'right'),
(50, 'MNT', 'Tugrik Mongol', '₮', 'right'),
(51, 'MOP', 'Pataca de Macau', 'MOP$', 'left'),
(52, 'MXN', 'Peso Mexicano', '$', 'left'),
(53, 'MYR', 'Ringgit Malaio', 'RM', 'left'),
(54, 'NGN', 'Naira Nigeriana', '₦', 'left'),
(55, 'NOK', 'Coroa Norueguesa', 'kr', 'right'),
(56, 'NPR', 'Rupia Nepalesa', 'Rs', 'right'),
(57, 'NZD', 'Dólar Neozelandês', '$', 'left'),
(58, 'OMR', 'Rial Omanense', '﷼', 'right'),
(59, 'PEN', 'Sol Peruano', 'S/', 'left'),
(60, 'PHP', 'Peso Filipino', '₱', 'left'),
(61, 'PKR', 'Rupia Paquistanesa', 'Rs', 'right'),
(62, 'PLN', 'Złoty Polonês', 'zł', 'right'),
(63, 'QAR', 'Rial Catarense', '﷼', 'right'),
(64, 'RON', 'Leu Romeno', 'lei', 'right'),
(65, 'RUB', 'Rublo Russo', '₽', 'right'),
(66, 'SAR', 'Riyal Saudita', '﷼', 'right'),
(67, 'SEK', 'Coroa Sueca', 'kr', 'right'),
(68, 'SGD', 'Dólar de Singapura', '$', 'left'),
(69, 'SYP', 'Libra Síria', '£', 'right'),
(70, 'THB', 'Baht Tailandês', '฿', 'left'),
(71, 'TND', 'Dinar Tunisiano', 'د.ت', 'right'),
(72, 'TRY', 'Lira Turca', '₺', 'left'),
(73, 'TWD', 'Novo Dólar Taiwanês', 'NT$', 'left'),
(74, 'TZS', 'Xelim Tanzaniano', 'TSh', 'right'),
(75, 'UAH', 'Hryvnia Ucraniana', '₴', 'right'),
(76, 'USD', 'Dólar Americano', '$', 'left'),
(77, 'UYU', 'Peso Uruguaio', '$', 'left'),
(78, 'UZS', 'Som Uzbeque', 'лв', 'right'),
(79, 'VES', 'Bolívar Venezuelano', 'Bs', 'right'),
(80, 'VND', 'Dong Vietnamita', '₫', 'right'),
(81, 'ZAR', 'Rand Sul-Africano', 'R', 'left'),
(82, 'ZMW', 'Kwacha Zambiano', 'ZK', 'right');

-- --------------------------------------------------------

--
-- Estrutura para tabela `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `bi` varchar(50) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `employees`
--

INSERT INTO `employees` (`id`, `company_id`, `name`, `bi`, `position`, `salary`, `status`, `created_at`) VALUES
(1, 1, 'Carlos Manuel', '002345678LA034', 'Analista de RH', 350000.00, 'ativo', '2025-05-10 22:57:14'),
(2, 1, 'Maria João', '004567891LA012', 'Gerente Financeira', 550000.00, 'ativo', '2025-05-10 22:57:14'),
(3, 1, 'João Pedro', '001234567LA056', 'Técnico de TI', 280000.00, 'ativo', '2025-05-10 22:57:14'),
(4, 1, 'Ana Luísa', '005432198LA087', 'Auxiliar Administrativo', 200000.00, 'inativo', '2025-05-10 22:57:14'),
(5, 1, 'Miguel António', '003456781LA032', 'Coordenador de Projetos', 470000.00, 'ativo', '2025-05-10 22:57:14'),
(6, 1, 'Laura Isabel', '006543219LA076', 'Secretária', 220000.00, 'ativo', '2025-05-10 22:57:14'),
(7, 1, 'Tiago Domingos', '007654321LA045', 'Engenheiro de Software', 600000.00, 'ativo', '2025-05-10 22:57:14'),
(8, 1, 'Helena Cristina', '008765432LA090', 'Contadora', 330000.00, 'inativo', '2025-05-10 22:57:14'),
(9, 1, 'Bruno Alexandre', '009876543LA022', 'Designer Gráfico', 310000.00, 'ativo', '2025-05-10 22:57:14'),
(10, 1, 'Sandra Patrícia', '010987654LA011', 'Analista Comercial', 360000.00, 'ativo', '2025-05-10 22:57:14'),
(11, 1, 'Guilherme dos Santos Rego', '767868778678', 'Analista', 78988.00, 'ativo', '2025-05-10 23:01:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `invoices`
--

DROP TABLE IF EXISTS `invoices`;
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `invoices`
--

INSERT INTO `invoices` (`id`, `contact_id`, `company_id`, `status`, `issue_date`, `due_date`, `reference`, `observation`, `series`, `retention`, `retention_value`, `currency`, `manual_exchange_rate`, `total_sum`, `total_discount`, `subtotal_without_tax`, `total_tax`, `final_total`, `converted_total`, `user_id`, `created_at`) VALUES
(36, 9, 1, 1, '2025-02-16', 32, '0004', 'What is Lorem Ipsum?\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\r\n\r\nWhy do we use it?\r\nIt is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).\r\n\r\n', '2025', 3.11, 1395.16, 'NGN', 1.62, 46100, 1239.6, 44860.4, 3696, 47161.24, 76401.209, 3, '2025-02-16 13:07:31'),
(37, 11, 1, 1, '2025-02-16', 15, '', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\r\n\r\nWhy do we use it?\r\nIt is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).\r\n\r\n\r\nWhere does it come from?\r\nContrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.\r\n\r\nThe standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from \"de Finibus Bonorum et Malorum\" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.\r\n\r\nWhere can I get some?\r\nThere are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.\r\n\r\n5\r\n	paragraphs\r\n	words\r\n	bytes\r\n	lists\r\n	Start with \'Lorem\r\nipsum dolor sit amet...\'\r\n', '2025', 0, 0, 'AOA', 0, 1, 0, 1, 140, 1140, 0, 1, '2025-02-16 23:23:17'),
(38, 13, 1, 1, '2025-02-16', 15, '0005', '', '2025', 0, 0, 'AOA', 0, 120.1, 0, 120.1, 14, 120114, 0, 1, '2025-02-16 23:24:30'),
(39, 12, 1, 1, '2025-03-11', 15, '', 'teste', '2025', 0, 0, 'AOA', 0, 99.44, 0, 99.44, 13.921, 113361.6, 0, 3, '2025-03-11 17:42:06'),
(40, 9, 1, 1, '2025-03-24', 15, '', '', '2025', 0, 0, 'CAD', 0, 125, 50, 75, 0, 75, 0, 1, '2025-03-24 17:19:20'),
(41, 11, 1, 1, '2025-04-25', 30, '', '', '2025', 0, 0, 'HUF', 0, 10.25, 0, 10.25, 1.4, 11650, 0, 1, '2025-04-25 14:54:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` double NOT NULL,
  `tax` double DEFAULT NULL,
  `discount` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_id`, `quantity`, `unit_price`, `tax`, `discount`) VALUES
(66, 36, 4, 4, 50, 0, 3),
(67, 36, 5, 12, 75, 14, 12),
(68, 36, 6, 32, 120, 0, 1),
(69, 36, 7, 164, 90, 0, 2),
(70, 36, 8, 132, 200, 14, 3),
(71, 37, 2, 10, 100, 14, 0),
(72, 38, 2, 1, 100, 14, 0),
(73, 38, 6, 1000, 120, 0, 0),
(74, 39, 6, 5, 19888, 14, 0),
(75, 40, 4, 1, 50, 14, 100),
(76, 40, 5, 1, 75, 0, 0),
(77, 41, 4, 5, 50, 0, 0),
(78, 41, 6, 10, 1000, 14, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `invoice_status`
--

DROP TABLE IF EXISTS `invoice_status`;
CREATE TABLE `invoice_status` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(7) NOT NULL,
  `text_color` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `invoice_status`
--

INSERT INTO `invoice_status` (`id`, `name`, `color`, `text_color`) VALUES
(1, 'Rascunho', '#ff9933', '#ffffff'),
(2, 'Cancelado', '#a80000', '#ffffff'),
(3, 'Finalizado', '#003a8c', '#ffffff'),
(4, 'Pago', '#207d01', '#ffffff');

-- --------------------------------------------------------

--
-- Estrutura para tabela `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `id_company` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `unit` enum('service','unit') DEFAULT NULL,
  `retention` enum('apply','do_not_apply') NOT NULL,
  `unit_price` double NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'AOA',
  `tax` enum('14','exempt') DEFAULT NULL,
  `pvp` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `items`
--

INSERT INTO `items` (`id`, `id_company`, `code`, `description`, `unit`, `retention`, `unit_price`, `currency`, `tax`, `pvp`, `created_at`) VALUES
(2, 1, 'A001', 'Item de teste 1', 'unit', 'apply', 100, 'USD', '14', 120, '2025-01-31 18:38:47'),
(4, 1, 'C001', 'Produto A', 'unit', 'apply', 50, 'AOA', 'exempt', 60, '2025-01-31 19:00:00'),
(5, 1, 'C002', 'Produto B', 'unit', 'do_not_apply', 75, 'AOA', '14', 85, '2025-01-31 19:01:00'),
(6, 1, 'C003', 'Produto C', 'service', 'apply', 120, 'AOA', 'exempt', 140, '2025-01-31 19:02:00'),
(7, 1, 'C004', 'Produto D', 'unit', 'do_not_apply', 90, 'AOA', 'exempt', 100, '2025-01-31 19:03:00'),
(8, 1, 'C005', 'Produto E', 'service', 'apply', 200, 'AOA', '14', 220, '2025-01-31 19:04:00'),
(9, 1, 'C006', 'Produto F', 'unit', 'apply', 30, 'AOA', 'exempt', 40, '2025-01-31 19:05:00'),
(10, 1, 'C007', 'Produto G', 'service', 'do_not_apply', 180, 'AOA', '14', 195, '2025-01-31 19:06:00'),
(11, 1, 'C008', 'Produto H', 'unit', 'apply', 45, 'AOA', 'exempt', 55, '2025-01-31 19:07:00'),
(12, 1, 'C009', 'Produto I', 'service', 'apply', 220, 'AOA', '14', 250, '2025-01-31 19:08:00'),
(13, 1, 'C010', 'Produto J', 'unit', 'do_not_apply', 85, 'AOA', 'exempt', 95, '2025-01-31 19:09:00'),
(16, 1, 'D003', 'Serviço C', NULL, '', 130, 'AOA', NULL, 150, '2025-01-31 19:12:00'),
(17, 2, 'D004', 'Serviço D', 'unit', 'do_not_apply', 95, 'AOA', 'exempt', 105, '2025-01-31 19:13:00'),
(18, 2, 'D005', 'Serviço E', 'service', 'apply', 210, 'AOA', '14', 230, '2025-01-31 19:14:00'),
(19, 2, 'D006', 'Serviço F', 'unit', 'apply', 35, 'AOA', '14', 45, '2025-01-31 19:15:00'),
(20, 2, 'D007', 'Serviço G', 'service', 'do_not_apply', 190, 'AOA', 'exempt', 205, '2025-01-31 19:16:00'),
(21, 2, 'D008', 'Serviço H', 'unit', 'apply', 50, 'AOA', 'exempt', 60, '2025-01-31 19:17:00'),
(22, 2, 'D009', 'Serviço I', 'service', 'apply', 230, 'AOA', 'exempt', 260, '2025-01-31 19:18:00'),
(23, 2, 'D010', 'Serviço J', 'unit', 'do_not_apply', 90, 'AOA', '14', 100, '2025-01-31 19:19:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `payment_methods_contacts`
--

DROP TABLE IF EXISTS `payment_methods_contacts`;
CREATE TABLE `payment_methods_contacts` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `payment_methods_contacts`
--

INSERT INTO `payment_methods_contacts` (`id`, `code`, `name`) VALUES
(1, 'transferencia', 'Transferência bancária ou débito direto autorizado'),
(2, 'credito', 'Cartão de crédito'),
(3, 'debito', 'Cartão de débito'),
(4, 'cheque', 'Cheque bancário'),
(5, 'cheque_oferta', 'Cheque ou cartão oferta'),
(6, 'compensacao', 'Compensação de saldos de conta corrente'),
(7, 'dinheiro', 'Dinheiro eletrônico'),
(8, 'letra_comercial', 'Letra comercial'),
(9, 'numerario', 'Numerário'),
(10, 'permuta', 'Permuta de bens'),
(11, 'credito_doc', 'Crédito documentário internacional'),
(12, 'outros', 'Outros meios aqui não assinalados');

-- --------------------------------------------------------

--
-- Estrutura para tabela `payroll`
--

DROP TABLE IF EXISTS `payroll`;
CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `reference_month` varchar(7) NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `bonuses` decimal(10,2) DEFAULT 0.00,
  `discounts` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `status` enum('Pago','Pendente') DEFAULT 'Pendente',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `payroll`
--

INSERT INTO `payroll` (`id`, `employee_id`, `company_id`, `reference_month`, `base_salary`, `bonuses`, `discounts`, `net_salary`, `payment_date`, `status`, `created_at`) VALUES
(1, 11, 1, '04/2025', 12312312.00, 99999999.99, 21312312.00, 90999999.99, '0000-00-00', 'Pago', '2025-05-10 23:35:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `session_token`, `expires_at`, `created_at`) VALUES
(1, 3, '0c127e5e033eeaa8cdff9607ab9f0822', '2025-02-13 16:26:34', '2025-02-13 14:56:35'),
(2, 3, '8c5c51294e48b787c50745cf974b90ac', '2025-02-13 16:27:18', '2025-02-13 14:57:18'),
(3, 3, '8fb961ef36d86ddf233b22bee3fa77e8', '2025-02-13 16:28:08', '2025-02-13 14:58:08'),
(4, 3, 'c74f2bb61b9cf4ce38be7dd384b1df61', '2025-02-13 16:30:17', '2025-02-13 15:00:17'),
(5, 3, '09dccb413110136142054c75ba4e3276', '2025-02-13 16:32:04', '2025-02-13 15:02:04'),
(6, 3, '1065457340f6f2c11475f739e4b27619', '2025-02-13 16:32:12', '2025-02-13 15:02:12'),
(7, 3, '8f828f1c3b6af21365aff9a6456d61d2', '2025-02-13 17:07:51', '2025-02-13 15:37:51'),
(8, 3, 'c7ddd1dddf9bf9edd7889f931d8111be', '2025-02-13 17:08:49', '2025-02-13 15:38:49'),
(9, 3, 'cbd1a110667567badfeb6ba4cbbae35a', '2025-02-13 17:11:15', '2025-02-13 15:41:15'),
(10, 3, 'a770f76cbc14cf4694475fd59e9f9ad1', '2025-02-13 16:12:27', '2025-02-13 15:42:27'),
(11, 3, '576932fbd6c01508df52c16f5032d054', '2025-02-13 13:20:12', '2025-02-13 12:50:12'),
(12, 3, '227faece54ab1336907bbbdf38af5844', '2025-02-13 13:20:20', '2025-02-13 12:50:20'),
(13, 3, '863eb2fb741492b7cab94915dcdbe358', '2025-02-13 13:20:58', '2025-02-13 12:50:58'),
(14, 3, '1857a81f2a806fbb76ae978893bc129d', '2025-02-13 13:22:59', '2025-02-13 12:52:59'),
(15, 3, '8848debe9be206d847de1f656dcc9934', '2025-02-13 13:26:13', '2025-02-13 12:56:13'),
(16, 3, '6b7d8e300b897be7af4317a93fa8f0b4', '2025-02-14 13:04:58', '2025-02-13 13:04:58'),
(17, 3, 'ab61fcf3d4e2cac280fc8b7dc74c2928', '2025-02-14 13:06:24', '2025-02-13 13:06:24'),
(18, 3, '1b22a00823ac2c2ad311042389778a1c', '2025-02-13 16:08:06', '2025-02-13 15:38:06'),
(19, 3, '4d08c5979f5a8b8456d690283a687fb7', '2025-02-13 16:10:57', '2025-02-13 15:40:57'),
(20, 3, '19e4349f7d16ab8164300e0ba0bbef68', '2025-02-13 16:11:12', '2025-02-13 15:41:12'),
(21, 3, '8ad1691fe69130fc76b7c2c78c9a93ee', '2025-02-13 16:20:39', '2025-02-13 15:50:39'),
(22, 1, '0c28350b7830034903dbcbd2f168ffa7', '2025-02-13 17:27:47', '2025-02-13 16:57:47'),
(23, 3, '0ff11d6c0e2608354f1f33bb0aad22d3', '2025-02-16 09:49:58', '2025-02-16 09:19:58'),
(24, 3, '54c16c262a42bba76a407717f55fc939', '2025-02-16 09:53:04', '2025-02-16 09:23:04'),
(25, 3, 'be89c380f82aedbfeb4e811ec6064b55', '2025-02-16 10:23:38', '2025-02-16 09:53:38'),
(26, 3, 'e03f598e31a293ca221ddbe080d6c0c4', '2025-02-16 10:55:44', '2025-02-16 10:25:44'),
(27, 3, '4e53932edfe9c580359308901b6ccebb', '2025-02-16 11:28:39', '2025-02-16 10:58:39'),
(28, 3, '3bd8d381830024547ef7982c40c8d734', '2025-02-16 12:01:40', '2025-02-16 11:31:40'),
(29, 3, '633502b1a3e5b9d47fef81f025fb87b3', '2025-02-16 12:03:47', '2025-02-16 11:33:47'),
(30, 1, '14d2e8cbf45079038069ea206d1b8716', '2025-02-16 17:31:27', '2025-02-16 17:01:27'),
(31, 1, '15dd39d21ae3ad8bdc78901f8f85ce02', '2025-02-16 18:01:38', '2025-02-16 17:31:38'),
(32, 1, '58e2d91ea33846d4facdfb0a0e262e37', '2025-02-16 18:32:19', '2025-02-16 18:02:19'),
(33, 1, 'bb3d87fd895f0da11cbbf87ed5f65b73', '2025-02-16 19:03:27', '2025-02-16 18:33:27'),
(34, 1, 'f8a1dba63e163d6b1bfe23f09ac4c149', '2025-02-16 19:08:47', '2025-02-16 18:38:47'),
(35, 1, 'c57660038b1a51bacf4521bd6b518e81', '2025-02-16 19:39:01', '2025-02-16 19:09:01'),
(36, 1, '4fc1b3d497acad1dab72f1fb9b045ee4', '2025-02-16 20:10:29', '2025-02-16 19:40:29'),
(37, 1, 'e45b730fa53f44562f92bcf7a1f3b796', '2025-02-16 20:40:39', '2025-02-16 20:10:39'),
(38, 3, '327aacffc29b1c5aa6aecc0413a1e535', '2025-02-16 22:55:57', '2025-02-16 22:25:57'),
(39, 3, 'adadfc7207c4a928e913e6f472c7be69', '2025-03-26 22:52:37', '2025-02-16 22:57:45'),
(40, 3, 'c39fc498f70fed5d5960bd525e777b39', '2025-02-17 21:41:46', '2025-02-17 17:30:36'),
(41, 3, 'fa5fc2ffebbdaf80e0a44a1f830ec0c3', '2025-02-19 15:59:58', '2025-02-19 15:08:32'),
(42, 3, 'a8342260951065a3f7fddcaa0ceb5434', '2025-02-19 23:43:59', '2025-02-19 15:14:00'),
(43, 3, '17a28c6d7b0acbb794f391e8d6052223', '2025-02-20 15:15:38', '2025-02-20 14:45:25'),
(44, 3, '0e03432a91d760ab732bfac6c07a136e', '2025-02-21 11:41:37', '2025-02-21 11:11:34'),
(45, 3, '3e1a7d91d393c8ed67ae5872f6dd049c', '2025-02-21 13:08:37', '2025-02-21 12:38:30'),
(46, 7, '944d890fc17718f74f29eb8957c67f5d', '2025-03-05 12:37:26', '2025-03-05 11:55:59'),
(47, 7, '998100185b92670d6c284ab6c66f8cde', '2025-03-07 04:52:27', '2025-03-05 11:56:31'),
(48, 7, '4c8847a2a0dc103198841e8e3a870b67', '2025-03-07 04:29:06', '2025-03-07 03:57:38'),
(49, 4, '50096e65d0aacb97229e3e92e29af04d', '2025-03-07 13:27:44', '2025-03-07 12:57:20'),
(50, 1, '7dca2bf3ffe1b7fb07f7fe21daf16cdb', '2025-03-07 13:47:57', '2025-03-07 12:58:24'),
(51, 4, '8e2f9983789ceb381ad1a24d7b460bba', '2025-03-07 14:11:57', '2025-03-07 13:31:23'),
(52, 3, '7f752e5bad9c25b6c36e41f3fb5fe5b3', '2025-03-11 10:36:00', '2025-03-11 10:05:59'),
(53, 3, '9eabe0ff881e0b8e07a92a83c404f130', '2025-03-11 17:10:50', '2025-03-11 14:38:18'),
(54, 7, '5f209477c4e36c82de391d39439bb13b', '2025-03-16 18:51:10', '2025-03-16 18:18:10'),
(55, 7, 'e451723d64622ed93f9f8dfc41731eef', '2025-03-21 07:27:19', '2025-03-20 07:06:32'),
(56, 4, 'edec735a1eda061381935120189ef24f', '2025-03-20 12:43:10', '2025-03-20 12:11:47'),
(57, 3, 'f36b62cd75fb6345c879acb0e284d1d3', '2025-03-24 11:38:21', '2025-03-24 11:08:13'),
(58, 3, '88891f54ceda901d0d49f4a7a4afd0a0', '2025-03-24 12:15:43', '2025-03-24 11:45:43'),
(59, 4, '2c1ffb07fb2a44c0e99b31264c45e861', '2025-03-24 14:34:08', '2025-03-24 14:04:08'),
(60, 1, 'ef480f78c30e70a1a17f5217a8a2febe', '2025-03-24 14:59:31', '2025-03-24 14:04:32'),
(61, 1, 'b8192d4bea5979f469018bd35d7c4df9', '2025-03-24 16:02:00', '2025-03-24 14:16:34'),
(62, 1, 'bf57181f4797ea33ac9f2c94cc410163', '2025-03-25 11:34:18', '2025-03-25 11:04:18'),
(63, 1, '01e1956a6f873d80d617261461687784', '2025-04-01 10:29:59', '2025-04-01 09:52:05'),
(64, 3, 'f1d94c193d83ffe2ad039d980b4f188d', '2025-04-01 10:34:59', '2025-04-01 10:00:54'),
(65, 3, '3e68c23d5d9c1a5cdda576f113ecc471', '2025-04-16 08:51:22', '2025-04-11 08:30:35'),
(66, 3, 'aa9a4f6c8ef96033446ac6477314d7e0', '2025-04-11 09:28:27', '2025-04-11 08:50:01'),
(67, 3, '4a761802daec2137a9b093f54db4ac22', '2025-04-14 00:35:46', '2025-04-11 10:31:14'),
(68, 3, '4e925aa4758a1f7f7df1628da620ae99', '2025-04-11 11:46:01', '2025-04-11 10:32:37'),
(69, 3, 'a51d1268e39c670cdc2bdad793be6b22', '2025-04-11 11:47:08', '2025-04-11 10:42:52'),
(70, 1, '5e1cc0e24f6f8a1dd5dea22bb9d21330', '2025-04-11 16:24:09', '2025-04-11 15:53:59'),
(71, 1, '9eb1a2e90a3468120e332e10fe4f48e1', '2025-04-13 11:32:16', '2025-04-13 10:58:40'),
(72, 7, 'e4bca6f6d92e355396d3ef33eea1bb17', '2025-04-19 11:49:33', '2025-04-19 11:19:18'),
(73, 1, 'a5f8464b04bb27489cd32e9178273511', '2025-04-25 11:55:45', '2025-04-25 11:25:45'),
(74, 1, '958fa28d3c0216ed00ba2fe599910da5', '2025-04-25 13:26:54', '2025-04-25 11:50:55'),
(75, 3, 'c99dacd70c3e746a40f4e4a332efafd3', '2025-04-25 12:26:58', '2025-04-25 11:56:38'),
(76, 3, 'fdafe0ba88f39dd3fcc4083e139978d4', '2025-04-25 12:27:33', '2025-04-25 11:57:20'),
(77, 3, '2feebdb6fd94f66f05441164737f4cf8', '2025-04-26 00:24:26', '2025-04-25 23:54:26'),
(78, 3, 'da5cfdbb116180a916631878be6341bb', '2025-05-08 12:01:10', '2025-05-08 11:18:50'),
(79, 3, 'f9eda0814f1ca0ee87753ab332b280e6', '2025-05-08 20:17:00', '2025-05-08 19:39:42'),
(80, 3, 'f65325e128534cbf64370211b3a79677', '2025-05-12 15:03:33', '2025-05-08 21:34:53'),
(81, 3, '1ff309195c8d49526d1eb7a54e0e7524', '2025-05-08 23:10:50', '2025-05-08 22:40:47'),
(82, 3, 'db4a32ba149b2710c5bf4b9cbb1a4801', '2025-05-09 12:24:09', '2025-05-09 11:53:58'),
(83, 1, '2af8e0c11621e2f45ce00dcdf0e35493', '2025-05-10 15:09:28', '2025-05-10 14:30:35'),
(84, 1, 'cf64822923f3acb744c52015ddb0071c', '2025-05-10 16:52:35', '2025-05-10 16:13:14'),
(85, 1, 'a031848525db83c0934cafc97ca07f67', '2025-05-13 05:39:32', '2025-05-10 19:21:34'),
(86, 3, 'b970fe8d0877bde8e3f548955e1cbb3c', '2025-05-12 20:45:22', '2025-05-12 19:52:59'),
(87, 3, '35545e3797f6ccf90ff06d29e6133c66', '2025-05-12 20:42:28', '2025-05-12 20:01:18'),
(88, 3, '6edba049a5b1ef49645f331585b83c57', '2025-05-22 22:00:47', '2025-05-12 20:44:27'),
(89, 3, '779a7963dd98ee04193830b7474725a6', '2025-05-13 11:56:56', '2025-05-12 21:37:35'),
(90, 3, 'ff192e83b2a9a6ee6be2248982a6ca9c', '2025-05-14 02:20:52', '2025-05-13 00:02:14'),
(91, 3, 'f2af429d5b367d578f34a2cd79e4ac2a', '2025-05-13 13:21:00', '2025-05-13 12:28:53');

-- --------------------------------------------------------

--
-- Estrutura para tabela `stocks`
--

DROP TABLE IF EXISTS `stocks`;
CREATE TABLE `stocks` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `color` varchar(20) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'box'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `stocks`
--

INSERT INTO `stocks` (`id`, `company_id`, `name`, `description`, `image_url`, `created_at`, `color`, `icon`) VALUES
(1, 1, 'Escritório Central', 'Estoque principal de materiais de escritório.', 'https://source.unsplash.com/400x300/?office', '2025-05-09 00:38:25', '#4e73df', 'box'),
(2, 1, 'Cozinha Industrial', 'Insumos e mantimentos para produção alimentar.', 'https://source.unsplash.com/400x300/?kitchen', '2025-05-09 00:38:25', '#1cc88a', 'box'),
(3, 1, 'Manutenção Predial', 'Ferramentas e peças para reparos e manutenção.', 'https://source.unsplash.com/400x300/?tools', '2025-05-09 00:38:25', '#f6c23e', 'box'),
(4, 1, 'Limpeza', 'Produtos de higiene e limpeza geral.', 'https://source.unsplash.com/400x300/?cleaning', '2025-05-09 00:38:25', '#e74a3b', 'box'),
(5, 1, 'Tecnologia', 'Equipamentos eletrônicos e periféricos.', 'https://source.unsplash.com/400x300/?technology', '2025-05-09 00:38:25', '#36b9cc', 'box'),
(8, 1, 'Meu estoque', 'Teste 1', '', '2025-05-13 03:08:36', '#27a2db', 'truck');

-- --------------------------------------------------------

--
-- Estrutura para tabela `stock_items`
--

DROP TABLE IF EXISTS `stock_items`;
CREATE TABLE `stock_items` (
  `id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'AOA',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `stock_items`
--

INSERT INTO `stock_items` (`id`, `stock_id`, `name`, `category`, `quantity`, `unit_price`, `currency`, `updated_at`) VALUES
(1, 1, 'Caneta Azul e Preta', 'Papelarias', 1492, 1.25, 'USD', '2025-05-13 03:06:20'),
(2, 1, 'Caderno Universitário', 'Papelaria', 500, 8.75, 'AOA', '2025-05-13 03:06:20'),
(3, 1, 'Grampeador', 'Acessórios', 10, 30.00, 'AOA', '2025-05-13 03:06:20'),
(4, 2, 'Arroz 5kg', 'Alimentos', 19, 18.90, 'AOA', '2025-05-12 23:25:39'),
(5, 2, 'Óleo de Soja', 'Alimentos', 35, 7.50, 'AOA', '2025-05-12 23:25:39'),
(6, 2, 'Feijão Preto 1kg', 'Alimentos', 40, 6.80, 'AOA', '2025-05-12 23:25:39'),
(7, 3, 'Chave de Fenda', 'Ferramentas', 25, 12.90, 'AOA', '2025-05-12 23:25:39'),
(8, 3, 'Parafusos 100un', 'Materiais', 15, 9.50, 'AOA', '2025-05-12 23:25:39'),
(9, 4, 'Água Sanitária', 'Produtos de Limpeza', 60, 4.30, 'AOA', '2025-05-12 23:25:39'),
(10, 4, 'Detergente Neutro', 'Produtos de Limpeza', 80, 2.99, 'AOA', '2025-05-12 23:25:39'),
(11, 5, 'Mouse Óptico USB', 'Periféricos', 12, 29.90, 'AOA', '2025-05-12 23:25:39'),
(12, 5, 'Teclado ABNT2', 'Periféricos', 8, 49.90, 'AOA', '2025-05-12 23:25:39'),
(13, 5, 'Notebook Dell i5', 'Equipamentos', 2, 3500.00, 'AOA', '2025-05-12 23:25:39'),
(15, 1, 'Abc', 'Acs', 2, 212.00, 'AOA', '2025-05-13 03:06:20'),
(16, 1, 'Caneta Azul', 'Papelaria', 151, 1.20, 'AOA', '2025-05-13 03:06:20'),
(17, 1, 'Caderno Universitário', 'Papelaria', 50, 8.75, 'AOA', '2025-05-13 03:06:20'),
(18, 1, 'Grampeador', 'Acessórios', 10, 15.00, 'AOA', '2025-05-13 03:06:20'),
(19, 1, 'Furador de Papel', 'Acessórios', 5, 25.50, 'AOA', '2025-05-13 03:06:20'),
(20, 1, 'Bloco de Notas', 'Papelaria', 100, 2.80, 'AOA', '2025-05-13 03:06:20'),
(21, 1, 'Marcador de Texto', 'Papelaria', 60, 3.90, 'AOA', '2025-05-13 03:06:20'),
(22, 1, 'Lápis Preto HB', 'Papelaria', 200, 0.80, 'AOA', '2025-05-13 03:06:20'),
(23, 2, 'Arroz 5kg', 'Alimentos', 20, 18.90, 'AOA', '2025-05-12 23:25:39'),
(24, 2, 'Feijão Preto 1kg', 'Alimentos', 30, 7.50, 'AOA', '2025-05-12 23:25:39'),
(25, 2, 'Macarrão Espaguete', 'Alimentos', 40, 4.20, 'AOA', '2025-05-12 23:25:39'),
(26, 2, 'Óleo de Soja', 'Alimentos', 25, 7.90, 'AOA', '2025-05-12 23:25:39'),
(27, 2, 'Sal Refinado', 'Condimentos', 15, 2.30, 'AOA', '2025-05-12 23:25:39'),
(28, 2, 'Açúcar Cristal', 'Alimentos', 20, 5.10, 'AOA', '2025-05-12 23:25:39'),
(29, 2, 'Farinha de Trigo', 'Alimentos', 18, 6.00, 'AOA', '2025-05-12 23:25:39'),
(30, 3, 'Chave de Fenda', 'Ferramentas', 20, 12.90, 'AOA', '2025-05-12 23:25:39'),
(31, 3, 'Martelo', 'Ferramentas', 10, 19.90, 'AOA', '2025-05-12 23:25:39'),
(32, 3, 'Alicate Universal', 'Ferramentas', 12, 24.50, 'AOA', '2025-05-12 23:25:39'),
(33, 3, 'Parafusos (100un)', 'Materiais', 30, 9.80, 'AOA', '2025-05-12 23:25:39'),
(34, 3, 'Fita Isolante', 'Materiais', 25, 3.50, 'AOA', '2025-05-12 23:25:39'),
(35, 3, 'Chave Inglesa', 'Ferramentas', 8, 34.00, 'AOA', '2025-05-12 23:25:39'),
(36, 3, 'Trena 5m', 'Acessórios', 15, 11.90, 'AOA', '2025-05-12 23:25:39'),
(37, 4, 'Água Sanitária', 'Produtos de Limpeza', 50, 4.30, 'AOA', '2025-05-12 23:25:39'),
(38, 4, 'Detergente Neutro', 'Produtos de Limpeza', 60, 2.99, 'AOA', '2025-05-12 23:25:39'),
(39, 4, 'Desinfetante Lavanda', 'Produtos de Limpeza', 30, 5.90, 'AOA', '2025-05-12 23:25:39'),
(40, 4, 'Sabão em Pó 1kg', 'Produtos de Limpeza', 25, 8.40, 'AOA', '2025-05-12 23:25:39'),
(41, 4, 'Esponja Multiuso', 'Acessórios', 40, 1.80, 'AOA', '2025-05-12 23:25:39'),
(42, 4, 'Balde 10L', 'Acessórios', 10, 12.00, 'AOA', '2025-05-12 23:25:39'),
(43, 4, 'Pano de Chão', 'Acessórios', 35, 3.10, 'AOA', '2025-05-12 23:25:39'),
(44, 5, 'Mouse Óptico USB', 'Periféricos', 20, 29.90, 'AOA', '2025-05-12 23:25:39'),
(45, 5, 'Teclado ABNT2', 'Periféricos', 15, 49.90, 'AOA', '2025-05-12 23:25:39'),
(46, 5, 'Monitor 21.5\"', 'Equipamentos', 5, 750.00, 'AOA', '2025-05-12 23:25:39'),
(47, 5, 'Notebook i5 8GB RAM', 'Equipamentos', 3, 3200.00, 'AOA', '2025-05-12 23:25:39'),
(48, 5, 'Cabo HDMI 1.5m', 'Acessórios', 25, 18.50, 'AOA', '2025-05-12 23:25:39'),
(49, 5, 'Pendrive 32GB', 'Acessórios', 30, 35.00, 'AOA', '2025-05-12 23:25:39'),
(50, 5, 'Estabilizador 500VA', 'Equipamentos', 7, 189.00, 'AOA', '2025-05-12 23:25:39'),
(51, 8, 'Israel', '', 2, 5288.00, 'AOA', '2025-05-13 15:30:00'),
(52, 8, 'Israel', '', 4, 352.25, 'AOA', '2025-05-13 15:30:00'),
(53, 8, 'Teste', '', 0, 0.00, 'AOA', '2025-05-13 15:30:00'),
(54, 8, 'Teste', '', 0, 0.00, 'AOA', '2025-05-13 15:30:00'),
(55, 8, 'A', '', 0, 0.00, 'AOA', '2025-05-13 15:30:00'),
(56, 8, 'A', '', 0, 0.00, 'AOA', '2025-05-13 15:30:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(1000) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('Masculino','Feminino','Outro','prefer_not_to_say') NOT NULL,
  `identification_type` int(11) NOT NULL,
  `identification` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `address_number` varchar(255) NOT NULL,
  `address_neighborhood` varchar(255) NOT NULL,
  `address_district` varchar(255) NOT NULL,
  `zip_code` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `blocked` tinyint(1) NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT 'default.png',
  `lang` varchar(255) NOT NULL DEFAULT 'angola',
  `currency` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `adm` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `phone`, `email`, `password`, `gender`, `identification_type`, `identification`, `address`, `address_number`, `address_neighborhood`, `address_district`, `zip_code`, `country`, `blocked`, `image`, `lang`, `currency`, `is_active`, `adm`, `created_at`, `updated_at`) VALUES
(1, 'grego', 'GUILHERME DOS SANTOS REGO', '21981364724', 'guilherme.s.rego@gmail.com', '$2y$10$DQhSJskdTJHUjUXSs4qNEOJUXG62ApnoVR30jnmwDIwiZfZo.rUUy', 'Masculino', 0, '', '', '', '', '', '', 'Angola', 0, 'profile_67ab3c522c8d1.jpg', 'angola', 'AOA', 1, 0, '2025-01-29 12:29:17', '2025-03-24 17:04:20'),
(3, 'iscosta', 'Israel Alessandro Souza da Costa', '21981364724', 'learsiasc99@gmail.com', '$2y$10$JMjCYakAGbsmn0fxlYyd6eXKLemQoQ9HL6QHy9ylhIM3aHsC20v2q', 'Masculino', 0, '', '', '', '', '', '', 'Angola', 0, 'profile_67a8a9e92cfd9.png', 'angola', 'AOA', 1, 0, '2025-01-29 12:29:17', '2025-02-11 13:30:24'),
(4, 'grego2', 'Guilherme dos Santos Rego', '21981776676', 'guilherme2@gmail.com', '$2y$10$DQhSJskdTJHUjUXSs4qNEOJUXG62ApnoVR30jnmwDIwiZfZo.rUUy', 'Masculino', 0, '', '', '', '', '', '', 'Angola', 0, 'default.png', 'brasil', 'AOA', 1, 0, '2025-01-30 11:45:39', '2025-02-11 13:30:27'),
(5, 'RobertoCardoso', 'Roberto Cardoso', '21123456789', 'robertocardoso.brasil@gmail.com', '$2y$10$JMjCYakAGbsmn0fxlYyd6eXKLemQoQ9HL6QHy9ylhIM3aHsC20v2q', 'Masculino', 0, '', '', '', '', '', '', 'Angola', 0, 'default.png', 'angola', 'AOA', 1, 0, '2025-02-11 13:30:08', '2025-02-11 13:45:20'),
(7, 'WalissonAquino', 'Walisson Aquino', '1234567889', 'walissonprata@gmail.com', '$2y$10$JMjCYakAGbsmn0fxlYyd6eXKLemQoQ9HL6QHy9ylhIM3aHsC20v2q', 'Masculino', 0, '', '', '', '', '', '', 'Angola', 0, 'profile_67ab5c87e7d88.png', 'angola', 'AOA', 1, 0, '2025-02-11 13:47:40', '2025-02-11 14:19:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vacations`
--

DROP TABLE IF EXISTS `vacations`;
CREATE TABLE `vacations` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `type` enum('Férias','Licença Médica','Licença Maternidade','Outros') DEFAULT 'Férias',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Aprovado','Pendente','Rejeitado') DEFAULT 'Pendente',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `vacations`
--

INSERT INTO `vacations` (`id`, `employee_id`, `company_id`, `type`, `start_date`, `end_date`, `reason`, `status`, `created_at`) VALUES
(1, 11, 1, 'Férias', '2025-01-01', '2025-02-01', 'férias', 'Rejeitado', '2025-05-10 23:20:29');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `currency` (`currency`);

--
-- Índices de tabela `company_has_user`
--
ALTER TABLE `company_has_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Índices de tabela `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`code`);

--
-- Índices de tabela `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `iso_code` (`iso_code`);

--
-- Índices de tabela `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoices_ibfk_1` (`company_id`),
  ADD KEY `invoices_ibfk_2` (`contact_id`),
  ADD KEY `currency` (`currency`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `invoices_ibfk_5` (`status`);

--
-- Índices de tabela `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Índices de tabela `invoice_status`
--
ALTER TABLE `invoice_status`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `items_ibfk_1` (`id_company`),
  ADD KEY `items_ibfk_2` (`currency`);

--
-- Índices de tabela `payment_methods_contacts`
--
ALTER TABLE `payment_methods_contacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Índices de tabela `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Índices de tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stocks_company` (`company_id`);

--
-- Índices de tabela `stock_items`
--
ALTER TABLE `stock_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_id` (`stock_id`),
  ADD KEY `currency` (`currency`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `vacations`
--
ALTER TABLE `vacations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `company_has_user`
--
ALTER TABLE `company_has_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT de tabela `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de tabela `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de tabela `invoice_status`
--
ALTER TABLE `invoice_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `payment_methods_contacts`
--
ALTER TABLE `payment_methods_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT de tabela `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `stock_items`
--
ALTER TABLE `stock_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `vacations`
--
ALTER TABLE `vacations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`currency`) REFERENCES `currencies` (`iso_code`);

--
-- Restrições para tabelas `company_has_user`
--
ALTER TABLE `company_has_user`
  ADD CONSTRAINT `company_has_user_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `company_has_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `contact` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `currencies` (`iso_code`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_ibfk_5` FOREIGN KEY (`status`) REFERENCES `invoice_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`id_company`) REFERENCES `companies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`currency`) REFERENCES `currencies` (`iso_code`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `fk_stocks_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `stock_items`
--
ALTER TABLE `stock_items`
  ADD CONSTRAINT `stock_items_ibfk_1` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_items_ibfk_2` FOREIGN KEY (`currency`) REFERENCES `currencies` (`iso_code`);

--
-- Restrições para tabelas `vacations`
--
ALTER TABLE `vacations`
  ADD CONSTRAINT `vacations_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
