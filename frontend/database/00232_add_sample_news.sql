-- Creating the Carousel_Items table with JSON support for multilingual text
CREATE TABLE `Carousel_Items` (
    `carousel_item_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` JSON NOT NULL,
    `excerpt` JSON NOT NULL,
    `image_url` VARCHAR(255) NULL,
    `link` VARCHAR(255) NULL,
    `button_title` JSON NULL,
    `expiration_date` DATETIME NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabla para almacenar noticias en la plataforma';

-- Inserting initial news entries with multilingual support
INSERT INTO `Carousel_Items` (title, excerpt, image_url, link, button_title, expiration_date, status, user_id)
VALUES
  (
    '{"en": "Improve your programming skills", "es": "Mejora tus habilidades de programación", "pt": "Melhore suas habilidades de programação"}',
    '{"en": "omegaUp is a free educational platform that helps you improve your programming skills, used by thousands of students and teachers in Latin America. Is this the first time you use omegaUp?", "es": "omegaUp es una plataforma educativa gratuita que te ayuda a mejorar tus habilidades de programación, utilizada por miles de estudiantes y profesores en América Latina. ¿Es esta la primera vez que usas omegaUp?", "pt": "omegaUp é uma plataforma educacional gratuita que ajuda você a melhorar suas habilidades de programação, usada por milhares de alunos e professores na América Latina. É a primeira vez que você usa omegaUp?"}',
    "https://omegaup.com/media/homepage/carousel_slide_1.svg",
    "https://blog.omegaup.com/introduccion-a-omegaup-parte-0/",
    '{"en": "See the tutorial", "es": "Ver el tutorial", "pt": "Veja o tutorial"}',
    NULL,
    "active",
    1
  ),
  (
    '{"en": "Would you like to learn to program?", "es": "¿Te gustaría aprender a programar?", "pt": "Você gostaria de aprender a programar?"}',
    '{"en": "Join the free Introduction to C++ course on omegaUp. It consists of lessons, examples and problems spread over 7 modules", "es": "Únete al curso gratuito de Introducción a C++ en omegaUp. Consta de lecciones, ejemplos y problemas distribuidos en 7 módulos", "pt": "Junte-se ao curso gratuito de Introdução ao C++ no omegaUp. Ele consiste em aulas, exemplos e problemas distribuídos em 7 módulos"}',
    "https://omegaup.com/media/homepage/carousel_slide_2.svg",
    "https://omegaup.com/course/introduccion_a_cpp/",
    '{"en": "Enter here", "es": "Entra aquí", "pt": "Entre aqui"}',
    NULL,
    "active",
    1
  ),
  (
    '{"en": "Join our coders community", "es": "Únete a nuestra comunidad de programadores", "pt": "Junte-se à nossa comunidade de programadores"}',
    '{"en": "Join omegaUp´s Discord server and hang out with your community, get help and learn about new projects.", "es": "Únete al servidor de Discord de omegaUp y conéctate con la comunidad, obtén ayuda y entérate de nuevos proyectos.", "pt": "Junte-se ao servidor Discord do omegaUp e conecte-se com a comunidade, obtenha ajuda e saiba mais sobre novos projetos."}',
    "https://omegaup.com/media/homepage/discord_logo.svg",
    "https://discord.com/invite/K3JFd9d3wk",
    '{"en": "Join here", "es": "Únete aquí", "pt": "Junte-se aqui"}',
    NULL,
    "active",
    1
  );
