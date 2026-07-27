-- jmt86 - 07/27/2026
-- Stores meals imported from TheMealDB and meals created manually.

CREATE TABLE IF NOT EXISTS meals (
    id SERIAL PRIMARY KEY,
    api_meal_id VARCHAR(20),
    meal_name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    cuisine VARCHAR(100) NOT NULL,
    image_url VARCHAR(500) DEFAULT '',
    is_api BOOLEAN NOT NULL DEFAULT FALSE,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT unique_api_meal_id UNIQUE (api_meal_id)
);