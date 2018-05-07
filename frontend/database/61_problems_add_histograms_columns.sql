-- Add quality_histogram and difficulty_histogram columns to Problems table
ALTER TABLE Problems
  ADD COLUMN quality_histogram TEXT COMMENT 'Valores del histograma de calidad del problema.',
  ADD COLUMN difficulty_histogram TEXT COMMENT 'Valores del histograma de dificultad del problema.';