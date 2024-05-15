CREATE TABLE `Professor` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(255),
  `email` varchar(255) UNIQUE NOT NULL,
  `cpf` varchar(14) UNIQUE NOT NULL,
  `telefone` varchar(255),
  `senha` varchar(255),
  `cad_pendente` boolean DEFAULT true
);

CREATE TABLE `Aluno` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(255),
  `senha` varchar(255),
  `email` varchar(255) UNIQUE NOT NULL
);

CREATE TABLE `Aluno_Turma` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `aluno` int NOT NULL,
  `turma` int NOT NULL,
  `dados_aluno` json,
  `dados_turma` json
);

CREATE TABLE `Turma` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(255),
  `ano` year,
  `escola` int NOT NULL,
  `professor` int NOT NULL,
  `senha` varchar(255)
);

CREATE TABLE `Escola` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(255)
);

CREATE TABLE `Professor_Escola` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `escola` int NOT NULL,
  `professor` int NOT NULL
);

CREATE TABLE `Turma_Fase` (
  `id` int AUTO_INCREMENT,
  `turma` int,
  `fase` int,
  PRIMARY KEY (`id`, `turma`, `fase`)
);

CREATE TABLE `Fase` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `criador` varchar(255) NOT NULL,
  `dificuldade` char NOT NULL,
  `tempo_medio_seg` int,
  `contem` json,
  `vars` json
);

CREATE TABLE `Resposta` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `escolha` int NOT NULL,
  `data_hora` datetime DEFAULT (current_timestamp()),
  `certo` boolean,
  `quiz` int NOT NULL,
  `aluno` int NOT NULL
);

CREATE TABLE `Quiz` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `turma_fase` int NOT NULL,
  `pergunta` text NOT NULL,
  `contem` json
);

CREATE TABLE `Alternativa` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `quiz` int NOT NULL,
  `alt_correta` boolean NOT NULL,
  `descricao` text NOT NULL,
  `justificativa` text
);

CREATE TABLE `Log` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `aluno` int NOT NULL,
  `turma_fase` int NOT NULL,
  `detalhes` varchar(255) NOT NULL,
  `objeto` json,
  `tipo` varchar(255) NOT NULL,
  `comeco` datetime NOT NULL,
  `fim` datetime DEFAULT (current_timestamp())
);

CREATE TABLE `ElementosTabelaPeriodica` (
  `sigla` varchar(10) PRIMARY KEY,
  `objeto` json
);

CREATE TABLE `Administrador` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(255),
  `email` varchar(255) UNIQUE NOT NULL,
  `cpf` varchar(14) UNIQUE NOT NULL,
  `telefone` varchar(255),
  `senha` varchar(255),
  `tipo` varchar(50)
);

ALTER TABLE `Aluno_Turma` ADD FOREIGN KEY (`aluno`) REFERENCES `Aluno` (`id`) ON DELETE CASCADE;

ALTER TABLE `Aluno_Turma` ADD FOREIGN KEY (`turma`) REFERENCES `Turma` (`id`) ON DELETE CASCADE;

ALTER TABLE `Turma` ADD FOREIGN KEY (`escola`) REFERENCES `Escola` (`id`) ON DELETE CASCADE;

ALTER TABLE `Turma` ADD FOREIGN KEY (`professor`) REFERENCES `Professor` (`id`) ON DELETE CASCADE;

ALTER TABLE `Professor_Escola` ADD FOREIGN KEY (`escola`) REFERENCES `Escola` (`id`) ON DELETE CASCADE;

ALTER TABLE `Professor_Escola` ADD FOREIGN KEY (`professor`) REFERENCES `Professor` (`id`) ON DELETE CASCADE;

ALTER TABLE `Turma_Fase` ADD FOREIGN KEY (`turma`) REFERENCES `Turma` (`id`) ON DELETE CASCADE;

ALTER TABLE `Turma_Fase` ADD FOREIGN KEY (`fase`) REFERENCES `Fase` (`id`) ON DELETE CASCADE;

ALTER TABLE `Resposta` ADD FOREIGN KEY (`escolha`) REFERENCES `Alternativa` (`id`) ON DELETE CASCADE;

ALTER TABLE `Resposta` ADD FOREIGN KEY (`quiz`) REFERENCES `Quiz` (`id`) ON DELETE CASCADE;

ALTER TABLE `Resposta` ADD FOREIGN KEY (`aluno`) REFERENCES `Aluno` (`id`) ON DELETE CASCADE;

ALTER TABLE `Quiz` ADD FOREIGN KEY (`turma_fase`) REFERENCES `Turma_Fase` (`id`) ON DELETE CASCADE;

ALTER TABLE `Alternativa` ADD FOREIGN KEY (`quiz`) REFERENCES `Quiz` (`id`) ON DELETE CASCADE;

ALTER TABLE `Log` ADD FOREIGN KEY (`aluno`) REFERENCES `Aluno` (`id`) ON DELETE CASCADE;

ALTER TABLE `Log` ADD FOREIGN KEY (`turma_fase`) REFERENCES `Turma_Fase` (`id`) ON DELETE CASCADE;
