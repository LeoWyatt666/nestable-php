SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

DROP TABLE IF EXISTS `menu`;
CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rang` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

INSERT INTO `menu` (`id`, `rang`, `parent_id`, `name`, `description`) VALUES
(1, 0, 0, 'About us', ''),
(2, 0, 1, 'Person 1', ''),
(3, 0, 1, 'Person 2', ''),
(4, 0, 2, 'My CV', ''),
(5, 0, 0, 'Gallery', ''),
(6, 0, 0, 'Contact us', ''),
(7, 0, 2, 'My pictures', ''),
(8, 0, 2, 'Contactinfo', '');
