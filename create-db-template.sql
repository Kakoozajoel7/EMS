create DATABASE event_mgmt_db;
use event_mgmt_db;
create table users(
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(250),
    Email VARCHAR(250) UNIQUE,
    Password VARCHAR(250),
    Phone VARCHAR(20),
    RegNo VARCHAR(50),
    Gender CHAR(10),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
create table admin(
    Id INT  PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(250),
    Email VARCHAR(250) UNIQUE,
    Password VARCHAR(250)
);
create table events(
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(250),
    Category VARCHAR(100),
    Description TEXT,
    EventDate DATE,
    StartTime TIME,
    EndTime TIME,
    Venue VARCHAR(250),
    Organiser VARCHAR(250),
    RSVPDeadline DATE,
    CreatedBy INT,
    FOREIGN KEY (CreatedBy) REFERENCES admin(Id),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
create table RSVP(
    Id INT PRIMARY KEY AUTO_INCREMENT,
    UserId INT, FOREIGN KEY (UserId) REFERENCES users(Id),
    EventId INT, FOREIGN KEY (EventId) REFERENCES events(Id),
    RSVPDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (UserId, EventId)
);
USE event_mgmt_db;
INSERT INTO users (Id, Name, Email, Password, Phone, RegNo, Gender) VALUES (
    1, 'John Doe', 'john.doe@example.com', 'password123', '1234567890', '2024_B071_12743', 'M'
);
INSERT INTO events (Id, Name, Category, Description, EventDate, StartTime, EndTime, Venue, Organiser, RSVPDeadline, CreatedBy) VALUES (
    1, 'Tech Conference 2024', 'Technology', 'A conference about the latest in tech.', '2024-10-15', '09:00:00', '17:00:00', 'Convention Center', 'TechOrg', '2024-10-01', 1
),
(
    2, 'Art Workshop', 'Art', 'A workshop for art enthusiasts.', '2024-11-20', '10:00:00', '16:00:00', 'Art Studio', 'Creative Minds', '2024-11-10', 1
);
USE event_mgmt_db;
ALTER TABLE users ADD COLUMN Role VARCHAR(20) DEFAULT 'student';
USE event_mgmt_db;
select * from users;
USE event_mgmt_db;
ALTER TABLE events ADD COLUMN Image VARCHAR(255);
USE event_mgmt_db;
SELECT * FROM users;
USE event_mgmt_db;
SELECT * FROM admin;
insert into admin (Id, Name, Email, Password) VALUES (
    1, 'Admin', 'admin@example.com', 'admin123'
);
USE event_mgmt_db;
SELECT * FROM users;