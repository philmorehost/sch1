--
-- Table structure for table `sm_cleanup_activated_schools`
--

CREATE TABLE `sm_cleanup_activated_schools` (
  `id` int(11) NOT NULL,
  `school_id_number` varchar(255) NOT NULL,
  `date_of_activation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sm_cleanup_activated_schools`
--
ALTER TABLE `sm_cleanup_activated_schools`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sm_cleanup_activated_schools`
--
ALTER TABLE `sm_cleanup_activated_schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;


--
-- Table structure for table `sm_bulk_report_card_activated_schools`
--

CREATE TABLE `sm_bulk_report_card_activated_schools` (
  `id` int(11) NOT NULL,
  `school_id_number` varchar(255) NOT NULL,
  `date_of_activation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sm_bulk_report_card_activated_schools`
--
ALTER TABLE `sm_bulk_report_card_activated_schools`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sm_bulk_report_card_activated_schools`
--
ALTER TABLE `sm_bulk_report_card_activated_schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

--
-- Table structure for table `sm_addon_settings`
--

CREATE TABLE `sm_addon_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addon_name` varchar(255) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `bank_details` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `addon_name` (`addon_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

--
-- Table structure for table `sm_addon_requests`
--

CREATE TABLE `sm_addon_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id_number` varchar(255) NOT NULL,
  `addon_name` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

--
-- Alter table `sm_school_details`
--
ALTER TABLE `sm_school_details` ADD `next_term_begins` VARCHAR(255) NULL AFTER `language`, ADD `no_of_days_open` VARCHAR(255) NULL AFTER `next_term_begins`;
COMMIT;
