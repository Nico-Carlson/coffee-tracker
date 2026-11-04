ALTER TABLE tblBrew
  MODIFY coffeeId INT NOT NULL,
  ADD CONSTRAINT fk_brew_coffee
    FOREIGN KEY (coffeeId) REFERENCES tblCoffee(coffeeId)
    ON DELETE CASCADE;
