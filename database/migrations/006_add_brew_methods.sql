CREATE TABLE IF NOT EXISTS tblMethod (
    methodId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    methodName VARCHAR(50) NOT NULL,
    UNIQUE KEY uq_method_name (methodName)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tblMethod (methodName) VALUES
    ('Pour over'),
    ('French press'),
    ('AeroPress'),
    ('Espresso'),
    ('Moka pot'),
    ('Cold brew'),
    ('Automatic drip');

ALTER TABLE tblBrew ADD COLUMN methodId INT NULL;

UPDATE tblBrew
SET methodId = (SELECT methodId FROM tblMethod WHERE methodName = 'Pour over')
WHERE methodId IS NULL;
