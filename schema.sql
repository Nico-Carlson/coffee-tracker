CREATE TABLE IF NOT EXISTS `tblUser` (
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `tblCoffee` (
  `coffeeName` varchar(255) NOT NULL,
  `roaster` varchar(255) NOT NULL,
  `roast` varchar(255) NOT NULL,
  `process` varchar(255) NOT NULL,
  `notes` mediumtext NOT NULL,
  `coffeeId` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  PRIMARY KEY (`coffeeId`),
  UNIQUE KEY `userID` (`userID`,`coffeeName`),
  CONSTRAINT `fk_coffee_user` FOREIGN KEY (`userID`) REFERENCES `tblUser` (`userID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `tblBrew` (
  `coffeeGrams` int(11) NOT NULL,
  `waterGrams` int(11) DEFAULT NULL,
  `brewDate` date NOT NULL,
  `waterTemp` int(11) DEFAULT NULL,
  `grindSize` varchar(255) NOT NULL,
  `bloomTime` int(11) DEFAULT NULL,
  `totalSeconds` int(11) NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `brewNotes` mediumtext NOT NULL,
  `brewID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `coffeeId` int(11) NOT NULL,
  PRIMARY KEY (`brewID`),
  KEY `fk_brew_user` (`userID`),
  KEY `fk_brew_coffee` (`coffeeId`),
  CONSTRAINT `fk_brew_coffee` FOREIGN KEY (`coffeeId`) REFERENCES `tblCoffee` (`coffeeId`) ON DELETE CASCADE,
  CONSTRAINT `fk_brew_user` FOREIGN KEY (`userID`) REFERENCES `tblUser` (`userID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
