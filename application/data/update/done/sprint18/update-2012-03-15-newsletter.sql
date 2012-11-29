alter table newsletter_recipients add column optOutReason INT(11) default NULL;

create table newsletter_opt_out_reasons (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `reason`  VARCHAR(255)  NOT NULL ,
    `online` TINYINT(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;



INSERT INTO `newsletter_opt_out_reasons`
(`reason`,`online`)
VALUES
('Kein Interesse mehr',1);

INSERT INTO `newsletter_opt_out_reasons`
(`reason`,`online`)
VALUES
('Versand zu häufig',1);

INSERT INTO `newsletter_opt_out_reasons`
(`reason`,`online`)
VALUES
('Inhalte nicht ansprechend',1);

