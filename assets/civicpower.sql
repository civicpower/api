-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : sam. 27 fév. 2021 à 14:09
-- Version du serveur :  8.0.23-0ubuntu0.20.04.1
-- Version de PHP : 7.3.27-9+ubuntu20.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `civicpower`
--

-- --------------------------------------------------------

--
-- Structure de la table `ask_asker`
--

CREATE TABLE `ask_asker` (
  `asker_id` int UNSIGNED NOT NULL,
  `asker_user_id` int UNSIGNED NOT NULL,
  `asker_astyp_id` int UNSIGNED NOT NULL DEFAULT '2',
  `asker_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `asker_active` tinyint(1) NOT NULL DEFAULT '1',
  `asker_creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `ask_type`
--

CREATE TABLE `ask_type` (
  `astyp_id` int UNSIGNED NOT NULL,
  `astyp_lib` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `bal_ballot`
--

CREATE TABLE `bal_ballot` (
  `ballot_id` int UNSIGNED NOT NULL,
  `ballot_asker_id` int UNSIGNED NOT NULL,
  `ballot_title` varchar(255) NOT NULL,
  `ballot_description` text NOT NULL,
  `ballot_start` datetime NOT NULL,
  `ballot_duration_second` int UNSIGNED NOT NULL,
  `ballot_asap` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `ballot_can_change_vote` tinyint UNSIGNED NOT NULL,
  `ballot_see_results_live` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `ballot_active` tinyint(1) NOT NULL DEFAULT '1',
  `ballot_bstatus_id` int UNSIGNED NOT NULL DEFAULT '1',
  `ballot_rejection_reason` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `ballot_acceptation_reason` text NOT NULL,
  `ballot_shortcode` varchar(50) NOT NULL DEFAULT '',
  `ballot_engagement` text,
  `ballot_creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ballot_update_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ballot_rappel_done` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `ballot_share` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `bal_filter`
--

CREATE TABLE `bal_filter` (
  `bfilter_id` int UNSIGNED NOT NULL,
  `bfilter_ballot_id` int UNSIGNED NOT NULL,
  `bfilter_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `bfilter_email_domain` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `bfilter_phone_dial` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `bfilter_phone_national` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `bfilter_phone_international` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `bfilter_city_id` int UNSIGNED DEFAULT NULL,
  `bfilter_active` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `bfilter_all` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `bal_option`
--

CREATE TABLE `bal_option` (
  `option_id` int UNSIGNED NOT NULL,
  `option_question_id` int UNSIGNED NOT NULL,
  `option_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `option_description` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `option_can_be_deleted` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `option_can_be_disabled` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `option_rank` mediumint NOT NULL DEFAULT '0',
  `option_active` tinyint(1) NOT NULL DEFAULT '1',
  `option_creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `bal_question`
--

CREATE TABLE `bal_question` (
  `question_id` int UNSIGNED NOT NULL,
  `question_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `question_description` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `question_ballot_id` int UNSIGNED NOT NULL,
  `question_nb_vote_min` smallint UNSIGNED NOT NULL DEFAULT '1',
  `question_nb_vote_max` smallint UNSIGNED NOT NULL DEFAULT '1',
  `question_rank` mediumint NOT NULL DEFAULT '0',
  `question_active` tinyint(1) NOT NULL DEFAULT '1',
  `question_creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `bal_status`
--

CREATE TABLE `bal_status` (
  `bstatus_id` int UNSIGNED NOT NULL,
  `bstatus_lib` varchar(100) NOT NULL,
  `bstatus_creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `usr_user`
--

CREATE TABLE `usr_user` (
  `user_id` int UNSIGNED NOT NULL,
  `user_salt` varchar(255) NOT NULL,
  `user_firstname` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `user_lastname` varchar(100) DEFAULT NULL,
  `user_birthyear` smallint UNSIGNED DEFAULT NULL,
  `user_birthday` date DEFAULT NULL,
  `user_phone_national` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_phone_dial` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_phone_international` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_phone_national_pending` varchar(255) DEFAULT NULL,
  `user_phone_dial_pending` varchar(50) DEFAULT NULL,
  `user_phone_international_pending` varchar(255) DEFAULT NULL,
  `user_password` varchar(255) NOT NULL DEFAULT '',
  `user_country_code` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'fr',
  `user_city_id` int UNSIGNED DEFAULT NULL,
  `user_zipcode` varchar(10) DEFAULT NULL,
  `user_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `user_email_pending` varchar(255) DEFAULT NULL,
  `user_streetnum` varchar(50) NOT NULL DEFAULT '',
  `user_street` varchar(255) NOT NULL DEFAULT '',
  `user_nationality_country_id` int UNSIGNED NOT NULL DEFAULT '75',
  `user_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_last_connect` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_code_validation_phone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_code_validation_email` varchar(50) NOT NULL DEFAULT '',
  `user_emailcode_send` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user_nb_login` int UNSIGNED NOT NULL DEFAULT '0',
  `user_nb_fail` int UNSIGNED NOT NULL DEFAULT '0',
  `user_nb_active_ballot_allowed` int UNSIGNED NOT NULL DEFAULT '99',
  `user_is_admin` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `user_active` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user_creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_update_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_creation_ip` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `user_creation_agent` varchar(254) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `user_creation_referer` varchar(254) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `user_ban` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `user_max_sms_day` mediumint UNSIGNED NOT NULL DEFAULT '10',
  `user_welcome_sent` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `vot_vote`
--

CREATE TABLE `vot_vote` (
  `vote_id` int UNSIGNED NOT NULL,
  `vote_user_id` int UNSIGNED NOT NULL,
  `vote_option_id` int UNSIGNED NOT NULL,
  `vote_datetime` datetime NOT NULL,
  `vote_active` tinyint(1) NOT NULL DEFAULT '1',
  `vote_creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ask_asker`
--
ALTER TABLE `ask_asker`
  ADD PRIMARY KEY (`asker_id`),
  ADD KEY `asker_user_id` (`asker_user_id`),
  ADD KEY `asker_astyp_id` (`asker_astyp_id`);

--
-- Index pour la table `ask_type`
--
ALTER TABLE `ask_type`
  ADD PRIMARY KEY (`astyp_id`);

--
-- Index pour la table `bal_ballot`
--
ALTER TABLE `bal_ballot`
  ADD PRIMARY KEY (`ballot_id`),
  ADD KEY `ballot_asker_id` (`ballot_asker_id`),
  ADD KEY `ballot_bstatus_id` (`ballot_bstatus_id`);

--
-- Index pour la table `bal_filter`
--
ALTER TABLE `bal_filter`
  ADD PRIMARY KEY (`bfilter_id`),
  ADD KEY `filter_ballot_id` (`bfilter_ballot_id`),
  ADD KEY `bfilter_city_id` (`bfilter_city_id`);

--
-- Index pour la table `bal_option`
--
ALTER TABLE `bal_option`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `option_question_id` (`option_question_id`);

--
-- Index pour la table `bal_question`
--
ALTER TABLE `bal_question`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `question_ballot_id` (`question_ballot_id`);

--
-- Index pour la table `bal_status`
--
ALTER TABLE `bal_status`
  ADD PRIMARY KEY (`bstatus_id`);

--
-- Index pour la table `usr_user`
--
ALTER TABLE `usr_user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_city_id` (`user_city_id`);

--
-- Index pour la table `vot_vote`
--
ALTER TABLE `vot_vote`
  ADD PRIMARY KEY (`vote_id`),
  ADD KEY `vote_user_id` (`vote_user_id`),
  ADD KEY `vote_option_id` (`vote_option_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ask_asker`
--
ALTER TABLE `ask_asker`
  MODIFY `asker_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ask_type`
--
ALTER TABLE `ask_type`
  MODIFY `astyp_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bal_ballot`
--
ALTER TABLE `bal_ballot`
  MODIFY `ballot_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bal_filter`
--
ALTER TABLE `bal_filter`
  MODIFY `bfilter_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bal_option`
--
ALTER TABLE `bal_option`
  MODIFY `option_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bal_question`
--
ALTER TABLE `bal_question`
  MODIFY `question_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bal_status`
--
ALTER TABLE `bal_status`
  MODIFY `bstatus_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `usr_user`
--
ALTER TABLE `usr_user`
  MODIFY `user_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `vot_vote`
--
ALTER TABLE `vot_vote`
  MODIFY `vote_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `ask_asker`
--
ALTER TABLE `ask_asker`
  ADD CONSTRAINT `ask_asker_ibfk_1` FOREIGN KEY (`asker_astyp_id`) REFERENCES `ask_type` (`astyp_id`),
  ADD CONSTRAINT `ask_asker_ibfk_2` FOREIGN KEY (`asker_user_id`) REFERENCES `usr_user` (`user_id`);

--
-- Contraintes pour la table `bal_ballot`
--
ALTER TABLE `bal_ballot`
  ADD CONSTRAINT `bal_ballot_ibfk_1` FOREIGN KEY (`ballot_bstatus_id`) REFERENCES `bal_status` (`bstatus_id`),
  ADD CONSTRAINT `bal_ballot_ibfk_2` FOREIGN KEY (`ballot_asker_id`) REFERENCES `ask_asker` (`asker_id`);

--
-- Contraintes pour la table `bal_filter`
--
ALTER TABLE `bal_filter`
  ADD CONSTRAINT `bal_filter_ibfk_1` FOREIGN KEY (`bfilter_ballot_id`) REFERENCES `bal_ballot` (`ballot_id`);

--
-- Contraintes pour la table `bal_option`
--
ALTER TABLE `bal_option`
  ADD CONSTRAINT `bal_option_ibfk_1` FOREIGN KEY (`option_question_id`) REFERENCES `bal_question` (`question_id`);

--
-- Contraintes pour la table `bal_question`
--
ALTER TABLE `bal_question`
  ADD CONSTRAINT `bal_question_ibfk_1` FOREIGN KEY (`question_ballot_id`) REFERENCES `bal_ballot` (`ballot_id`);

--
-- Contraintes pour la table `vot_vote`
--
ALTER TABLE `vot_vote`
  ADD CONSTRAINT `vot_vote_ibfk_1` FOREIGN KEY (`vote_user_id`) REFERENCES `usr_user` (`user_id`),
  ADD CONSTRAINT `vot_vote_ibfk_2` FOREIGN KEY (`vote_option_id`) REFERENCES `bal_option` (`option_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
