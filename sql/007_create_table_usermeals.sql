-- jmt86 - 08/04/2026
-- Creates the many-to-many relationship between users and meals.

CREATE TABLE IF NOT EXISTS usermeals (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    meal_id INT NOT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (user_id, meal_id),

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (meal_id)
        REFERENCES meals(id)
        ON DELETE CASCADE
);