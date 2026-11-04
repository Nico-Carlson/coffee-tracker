ALTER TABLE tblBrew
  ADD COLUMN coffeeId INT NULL AFTER userID;

UPDATE tblBrew b
JOIN tblCoffee c
  ON c.userID = b.userID AND c.coffeeName = b.coffeeType
SET b.coffeeId = c.coffeeId;
