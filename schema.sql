CREATE TABLE IF NOT EXISTS tblUser (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(50),
    lastName VARCHAR(50),
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblCoffee (
    coffeeId INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    coffeeName VARCHAR(100) NOT NULL,
    roaster VARCHAR(100),
    roast ENUM('Light', 'Medium', 'Dark') NOT NULL,
    process VARCHAR(50),
    notes TEXT,
    CONSTRAINT fk_coffee_user FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE,
    UNIQUE KEY uq_user_coffee (userID, coffeeName)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblBrew (
    brewID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    coffeeType VARCHAR(100) NOT NULL,
    coffeeGrams INT NOT NULL,
    waterGrams INT DEFAULT NULL,
    waterTemp INT DEFAULT NULL,
    grindSize VARCHAR(50),
    bloomTime INT DEFAULT NULL,
    totalSeconds INT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    brewNotes TEXT,
    brewDate DATE NOT NULL,
    CONSTRAINT fk_brew_user FOREIGN KEY (userID) REFERENCES tblUser(userID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
