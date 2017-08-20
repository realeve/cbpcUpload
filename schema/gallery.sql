
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tbl_attach
-- ----------------------------
DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `tbl_attach` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_size` double DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `width` double DEFAULT NULL,
  `height` double DEFAULT NULL,
  `rec_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;