
CREATE DATABASE IF NOT EXISTS github_finder;

USE github_finder;

CREATE TABLE IF NOT EXISTS search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(39) NOT NULL,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO search_history (username)
VALUES ('octocat');