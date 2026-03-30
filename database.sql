-- ============================================
-- COSMOS NEWS - Portal de Astronomia
-- Banco de dados: portal_astronomia
-- ============================================

CREATE DATABASE IF NOT EXISTS portal_astronomia
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE portal_astronomia;

-- ============================================
-- Tabela: usuarios
-- ============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: categorias (tabela extra / diferenciação)
-- ============================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    icone VARCHAR(50) DEFAULT 'star',
    descricao TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: noticias
-- ============================================
CREATE TABLE noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(280) NOT NULL,
    resumo TEXT NULL,
    noticia TEXT NOT NULL,
    imagem VARCHAR(255) NULL,
    categoria_id INT NULL,
    autor_id INT NOT NULL,
    destaque TINYINT(1) DEFAULT 0,
    visualizacoes INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Dados iniciais: Categorias de Astronomia
-- ============================================
INSERT INTO categorias (nome, slug, icone, descricao) VALUES
('Planetas', 'planetas', 'globe', 'Notícias sobre planetas do sistema solar e exoplanetas'),
('Estrelas', 'estrelas', 'sun', 'Descobertas sobre estrelas, supernovas e nebulosas'),
('Galáxias', 'galaxias', 'star', 'Estudos sobre galáxias, Via Láctea e universo profundo'),
('Exploração Espacial', 'exploracao-espacial', 'rocket', 'Missões espaciais, NASA, SpaceX e agências espaciais'),
('Telescópios', 'telescopios', 'binoculars', 'James Webb, Hubble e observatórios terrestres'),
('Sistema Solar', 'sistema-solar', 'earth-americas', 'Sol, luas, asteroides e cometas do nosso sistema'),
('Buracos Negros', 'buracos-negros', 'bolt', 'Pesquisas sobre buracos negros e fenômenos extremos'),
('Astronautas', 'astronautas', 'user-astronaut', 'Vida no espaço, ISS e treinamento de astronautas');
