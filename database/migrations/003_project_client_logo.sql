-- Persomy : nom et logo du client sur les projets (carrousel de logos de la page d'accueil).

ALTER TABLE projects
  ADD COLUMN client_name VARCHAR(120) NULL AFTER main_image,
  ADD COLUMN client_logo VARCHAR(80) NULL AFTER client_name;
