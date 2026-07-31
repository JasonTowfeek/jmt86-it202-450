-- mp2446 | 2026-07-30
-- Creates the many-to-many relationship between users and books.

CREATE TABLE IF NOT EXISTS UserBooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_userbooks_user
        FOREIGN KEY (user_id)
        REFERENCES Users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_userbooks_book
        FOREIGN KEY (book_id)
        REFERENCES Books(id)
        ON DELETE CASCADE,

    CONSTRAINT uq_userbooks_user_book
        UNIQUE (user_id, book_id)
);