CREATE TABLE users (
	userId INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	nick VARCHAR(50) NOT NULL UNIQUE,
	password VARCHAR(32) NOT NULL,
	role VARCHAR(10) NOT NULL,
	email VARCHAR(50) NOT NULL UNIQUE,
	token VARCHAR(32) NULL UNIQUE
);

CREATE TABLE categories(
	categoryId INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(50) NOT NULL
);

CREATE TABLE apps(
	guid CHAR(36) NOT NULL PRIMARY KEY,
	name VARCHAR(50) NOT NULL,
	publisher INT NOT NULL,
	version VARCHAR(25) NOT NULL,
	description TEXT NULL,
	category INT NOT NULL,
	subcategory INT NULL,
	othercategory INT NULL,
	3dsx VARCHAR(255) NOT NULL,
	smdh VARCHAR(255) NOT NULL,
	rating TINYINT NOT NULL DEFAULT 0,
	downloads INT NOT NULL DEFAULT 0,

	FOREIGN KEY (publisher) REFERENCES users(userId),
	FOREIGN KEY (category) REFERENCES categories(categoryId),
	FOREIGN KEY (subcategory) REFERENCES categories(categoryId),
	FOREIGN KEY (othercategory) REFERENCES categories(categoryId)
);

CREATE TABLE ratings(
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	appGuid CHAR(36) NOT NULL,
	userId INT NOT NULL,
	rate TINYINT NOT NULL DEFAULT 0,

	FOREIGN KEY (appGuid) REFERENCES apps(guid),
	FOREIGN KEY (userId) REFERENCES users(userId)
);

CREATE TABLE developers(
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	appGuid CHAR(36) NOT NULL,
	developerId INT NOT NULL,
	nick VARCHAR(50) NULL,

	FOREIGN KEY (appGuid) REFERENCES apps(guid),
	FOREIGN KEY (developerId) REFERENCES users(userId)
);