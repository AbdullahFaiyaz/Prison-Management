-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2025 at 07:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prison_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

CREATE TABLE `assignment` (
  `work_ID` int(11) NOT NULL,
  `Prisoner_ID` int(11) DEFAULT NULL,
  `work_type` varchar(50) NOT NULL,
  `supervisor` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('Active','Completed','Terminated') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment`
--

INSERT INTO `assignment` (`work_ID`, `Prisoner_ID`, `work_type`, `supervisor`, `start_date`, `end_date`, `status`) VALUES
(1, 101, 'Woodworking', 'Officer Adams', '2024-01-10', NULL, 'Active'),
(2, 102, 'Plumbing', 'Officer Brown', '2024-01-15', NULL, 'Active'),
(3, 103, 'Welding', 'Officer Clark', '2024-02-01', NULL, 'Active'),
(4, 104, 'Carpentry', 'Officer Davis', '2024-02-10', NULL, 'Active'),
(5, 105, 'Electrical Work', 'Officer Evans', '2024-03-01', NULL, 'Active'),
(6, 106, 'Painting', 'Officer Foster', '2024-03-15', NULL, 'Active'),
(7, 107, 'Masonry', 'Officer Green', '2024-04-01', NULL, 'Active'),
(8, 108, 'Culinary Arts', 'Officer Hill', '2024-04-15', NULL, 'Active'),
(9, 109, 'Gardening', 'Officer Irwin', '2024-05-01', NULL, 'Active'),
(10, 110, 'Metal Work', 'Officer Jones', '2024-05-15', NULL, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `behavior_report`
--

CREATE TABLE `behavior_report` (
  `Report_ID` int(11) NOT NULL,
  `Prisoner_ID` int(11) NOT NULL,
  `Report_Date` date NOT NULL,
  `Reported_By` varchar(100) NOT NULL,
  `Behavior_Type` enum('Good','Neutral','Poor','Violent','Exceptional') NOT NULL,
  `Behavior_Notes` text NOT NULL,
  `Action_Taken` text DEFAULT NULL,
  `Follow_Up_Required` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `behavior_report`
--

INSERT INTO `behavior_report` (`Report_ID`, `Prisoner_ID`, `Report_Date`, `Reported_By`, `Behavior_Type`, `Behavior_Notes`, `Action_Taken`, `Follow_Up_Required`) VALUES
(1, 101, '2024-01-05', 'Officer Adams', 'Poor', 'Displayed aggressive behavior during meal time', 'Assigned to anger management sessions', 1),
(2, 102, '2024-01-10', 'Officer Brown', 'Exceptional', 'Cooperative during work assignment', 'None', 0),
(3, 103, '2024-01-15', 'Officer Clark', 'Violent', 'Involved in fight with another inmate', '10 days solitary confinement', 1),
(4, 104, '2024-01-20', 'Officer Davis', 'Neutral', 'Quiet, keeps to himself', 'None', 0),
(5, 105, '2024-01-25', 'Officer Evans', 'Poor', 'Refused to follow instructions', 'Loss of privileges for 3 days', 1),
(6, 106, '2024-02-01', 'Officer Foster', 'Violent', 'Threatened staff member', '15 days solitary confinement', 1),
(7, 107, '2024-02-05', 'Officer Green', 'Good', 'Helped resolve conflict between inmates', 'Extra recreation time awarded', 0),
(8, 108, '2024-02-10', 'Officer Hill', 'Neutral', 'No issues reported', 'None', 0),
(9, 109, '2024-02-15', 'Officer Irwin', 'Poor', 'Found with contraband', 'Loss of visitation rights for 30 days', 1),
(10, 110, '2024-02-20', 'Officer Jones', 'Exceptional', 'Excellent participation in rehabilitation program', 'Consider for early parole review', 0);

-- --------------------------------------------------------

--
-- Table structure for table `cell`
--

CREATE TABLE `cell` (
  `Cell_ID` int(11) NOT NULL,
  `Cell_Number` varchar(10) NOT NULL,
  `Block` varchar(10) NOT NULL,
  `Capacity` int(11) NOT NULL,
  `Current_Occupancy` int(11) NOT NULL,
  `Status` enum('Occupied','Vacant','Maintenance') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cell`
--

INSERT INTO `cell` (`Cell_ID`, `Cell_Number`, `Block`, `Capacity`, `Current_Occupancy`, `Status`) VALUES
(1, 'A101', 'A', 2, 1, 'Occupied'),
(2, 'A102', 'A', 2, 1, 'Occupied'),
(3, 'A103', 'A', 2, 1, 'Occupied'),
(4, 'B101', 'B', 1, 1, 'Occupied'),
(5, 'B102', 'B', 1, 1, 'Occupied'),
(6, 'B103', 'B', 1, 1, 'Occupied'),
(7, 'C101', 'C', 3, 1, 'Occupied'),
(8, 'C102', 'C', 3, 1, 'Occupied'),
(9, 'C103', 'C', 3, 1, 'Occupied'),
(10, 'D101', 'D', 2, 1, 'Occupied');

-- --------------------------------------------------------

--
-- Table structure for table `complaint`
--

CREATE TABLE `complaint` (
  `Complaint_ID` int(11) NOT NULL,
  `Prisoner_ID` int(11) NOT NULL,
  `Complaint_Date` date NOT NULL,
  `Category` enum('Food','Medical','Safety','Hygiene','Other') NOT NULL,
  `Complaint_Details` text NOT NULL,
  `Status` enum('Open','In Progress','Resolved','Rejected') NOT NULL,
  `Resolution` text DEFAULT NULL,
  `Resolved_Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint`
--

INSERT INTO `complaint` (`Complaint_ID`, `Prisoner_ID`, `Complaint_Date`, `Category`, `Complaint_Details`, `Status`, `Resolution`, `Resolved_Date`) VALUES
(1, 101, '2024-01-05', 'Food', 'Food is often cold', 'Resolved', 'Kitchen staff instructed to serve food immediately after preparation', '2024-01-10'),
(2, 102, '2024-01-10', 'Medical', 'Did not receive medication on time', 'Resolved', 'Medication schedule adjusted', '2024-01-12'),
(3, 103, '2024-01-15', 'Safety', 'Feels threatened by cellmate', 'Resolved', 'Cell reassignment processed', '2024-01-18'),
(4, 104, '2024-01-20', 'Hygiene', 'Cell not cleaned regularly', 'In Progress', 'Cleaning schedule being reviewed', NULL),
(5, 105, '2024-01-25', 'Other', 'Request for additional books in library', 'Open', NULL, NULL),
(6, 106, '2024-02-01', 'Medical', 'Need to see doctor for migraines', 'Resolved', 'Appointment scheduled', '2024-02-05'),
(7, 107, '2024-02-05', 'Food', 'Allergic reaction to meal', 'Resolved', 'Allergy noted in file, special meals arranged', '2024-02-08'),
(8, 108, '2024-02-10', 'Safety', 'Broken lock on cell door', 'Resolved', 'Maintenance repaired lock', '2024-02-11'),
(9, 109, '2024-02-15', 'Hygiene', 'No hot water in shower', 'In Progress', 'Plumber scheduled', NULL),
(10, 110, '2024-02-20', 'Other', 'Request for religious materials', 'Open', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medical_record`
--

CREATE TABLE `medical_record` (
  `Medical_ID` int(11) NOT NULL,
  `Prisoner_ID` int(11) NOT NULL,
  `Record_Date` date NOT NULL,
  `Diagnosis` text NOT NULL,
  `Treatment` text NOT NULL,
  `Prescription` text DEFAULT NULL,
  `Doctor` varchar(100) NOT NULL,
  `Next_Checkup` date DEFAULT NULL,
  `Urgent_Flag` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_record`
--

INSERT INTO `medical_record` (`Medical_ID`, `Prisoner_ID`, `Record_Date`, `Diagnosis`, `Treatment`, `Prescription`, `Doctor`, `Next_Checkup`, `Urgent_Flag`) VALUES
(1, 101, '2024-01-05', 'Asthma, hypertension', 'Regular medication', 'Albuterol inhaler, Lisinopril 10mg daily', 'Dr. Smith', '2024-04-05', 1),
(2, 102, '2024-01-10', 'Diabetes Type 2', 'Blood sugar monitoring', 'Metformin 500mg twice daily', 'Dr. Johnson', '2024-04-10', 0),
(3, 103, '2024-01-15', 'Chronic back pain', 'Physical therapy', 'Ibuprofen 400mg as needed', 'Dr. Williams', '2024-04-15', 0),
(4, 104, '2024-01-20', 'Routine checkup', 'No issues found', 'None', 'Dr. Brown', '2024-07-20', 0),
(5, 105, '2024-01-25', 'High cholesterol', 'Dietary changes', 'Atorvastatin 20mg daily', 'Dr. Davis', '2024-04-25', 0),
(6, 106, '2024-02-01', 'Severe migraines', 'Neurological evaluation', 'Sumatriptan 50mg as needed', 'Dr. Miller', '2024-05-01', 1),
(7, 107, '2024-02-05', 'General health check', 'No significant findings', 'None', 'Dr. Wilson', '2024-08-05', 0),
(8, 108, '2024-02-10', 'History of cardiac issues', 'Cardiac monitoring', 'Atenolol 50mg daily', 'Dr. Moore', '2024-05-10', 1),
(9, 109, '2024-02-15', 'Penicillin allergy', 'Allergy noted in chart', 'Avoid penicillin antibiotics', 'Dr. Taylor', NULL, 0),
(10, 110, '2024-02-20', 'Epilepsy', 'Seizure management', 'Levetiracetam 500mg twice daily', 'Dr. Anderson', '2024-05-20', 1);

-- --------------------------------------------------------

--
-- Table structure for table `prisoner`
--

CREATE TABLE `prisoner` (
  `Prisoner_ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Age` int(11) NOT NULL,
  `Gender` varchar(10) NOT NULL,
  `Weight_kg` float NOT NULL,
  `Height_cm` float NOT NULL,
  `Address` text DEFAULT NULL,
  `Crime` varchar(255) NOT NULL,
  `Entry_Date` date NOT NULL,
  `Previous_History` text DEFAULT NULL,
  `Sentence_Duration` int(11) NOT NULL,
  `Parole_Eligibility` tinyint(1) DEFAULT NULL,
  `Release_Date` date DEFAULT NULL,
  `Cell_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prisoner`
--

INSERT INTO `prisoner` (`Prisoner_ID`, `Name`, `Age`, `Gender`, `Weight_kg`, `Height_cm`, `Address`, `Crime`, `Entry_Date`, `Previous_History`, `Sentence_Duration`, `Parole_Eligibility`, `Release_Date`, `Cell_ID`) VALUES
(101, 'John Doe', 35, 'Male', 78.5, 175, '123 Main St, Anytown', 'Theft, Assault', '2020-05-15', 'Theft, Assault', 10, 1, '2030-05-15', 1,),
(102, 'Michael Smith', 42, 'Male', 85.3, 182, '456 Oak Ave, Somewhere', 'Fraud, Embezzlement', '2022-10-20', 'Fraud, Embezzlement', 7, 1, '2029-10-20', 2,),
(103, 'David Johnson', 29, 'Male', 90.2, 178, '789 Pine Rd, Nowhere', 'Drug Possession', '2021-12-10', 'Drug Possession', 5, 1, '2026-12-10', 3,),
(104, 'James Brown', 50, 'Male', 95.8, 185, '321 Elm St, Anywhere', 'Murder', '2020-08-01', 'Murder', 25, 0, '2045-08-01', 4,),
(105, 'Robert Wilson', 38, 'Male', 80.1, 172, '654 Maple Dr, Elsewhere', 'Burglary, Vandalism', '2023-03-22', 'Burglary, Vandalism', 8, 1, '2031-03-22', 5,),
(106, 'William Taylor', 27, 'Male', 76, 168, '987 Cedar Ln, Someplace', 'Illegal Firearms', '2021-07-18', 'Illegal Firearms', 6, 1, '2027-07-18', 6,),
(107, 'Joseph Miller', 33, 'Male', 88.4, 180, '135 Birch Blvd, Nowhere', 'Bribery, Extortion', '2022-04-09', 'Bribery, Extortion', 12, 0, '2034-04-09', 7,),
(108, 'Christopher White', 45, 'Male', 92.6, 183, '246 Walnut Ct, Anywhere', 'Arson, Theft', '2022-06-25', 'Arson, Theft', 15, 1, '2037-06-25', 8,),
(109, 'Daniel Harris', 31, 'Male', 81.9, 175, '369 Spruce Way, Elsewhere', 'Hit and Run', '2021-11-30', 'Hit and Run', 4, 1, '2025-11-30', 9,),
(110, 'Matthew Martin', 39, 'Male', 86.7, 178, '482 Ash St, Somewhere', 'Kidnapping', '2022-02-14', 'Kidnapping', 20, 1, '2042-02-14', 10,);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `Staff_ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Position` varchar(50) NOT NULL,
  `Department` varchar(50) NOT NULL,
  `Contact` varchar(20) NOT NULL,
  `Hire_Date` date NOT NULL,
  `Shift` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`Staff_ID`, `Name`, `Position`, `Department`, `Contact`, `Hire_Date`, `Shift`) VALUES
(1, 'Officer Adams', 'Correctional Officer', 'Security', '555-0101', '2020-05-15', 'Morning'),
(2, 'Officer Brown', 'Correctional Officer', 'Security', '555-0102', '2019-10-20', 'Evening'),
(3, 'Officer Clark', 'Correctional Officer', 'Security', '555-0103', '2021-03-10', 'Night'),
(4, 'Officer Davis', 'Correctional Officer', 'Security', '555-0104', '2020-08-01', 'Morning'),
(5, 'Officer Evans', 'Correctional Officer', 'Security', '555-0105', '2022-01-15', 'Evening'),
(6, 'Officer Foster', 'Correctional Officer', 'Security', '555-0106', '2021-07-18', 'Night'),
(7, 'Officer Green', 'Correctional Officer', 'Security', '555-0107', '2020-11-30', 'Morning'),
(8, 'Officer Hill', 'Correctional Officer', 'Security', '555-0108', '2021-06-25', 'Evening'),
(9, 'Officer Irwin', 'Correctional Officer', 'Security', '555-0109', '2022-02-14', 'Night'),
(10, 'Officer Jones', 'Correctional Officer', 'Security', '555-0110', '2020-04-09', 'Morning');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','officer','medical','visitor') NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', 'admin123', 'admin'),
('officer', 'officer123', 'officer'),
('medical', 'medical123', 'medical'),
('visitor', 'visitor123', 'visitor');

-- --------------------------------------------------------

--
-- Table structure for table `visitor`
--

CREATE TABLE `visitor` (
  `Visitor_ID` int(11) NOT NULL,
  `Prisoner_ID` int(11) NOT NULL,
  `Visitor_Name` varchar(100) NOT NULL,
  `Relationship` varchar(50) NOT NULL,
  `Visit_Date` date NOT NULL,
  `Visit_Time` time NOT NULL,
  `Duration_mins` int(11) NOT NULL,
  `Status` enum('Approved','Pending','Denied','Completed') NOT NULL,
  `Notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitor`
--

INSERT INTO `visitor` (`Visitor_ID`, `Prisoner_ID`, `Visitor_Name`, `Relationship`, `Visit_Date`, `Visit_Time`, `Duration_mins`, `Status`, `Notes`) VALUES
(1, 101, 'Jane Doe', 'Wife', '2024-03-01', '10:00:00', 30, 'Completed', 'Brought legal documents'),
(2, 101, 'John Doe Jr.', 'Son', '2024-03-15', '14:00:00', 30, 'Approved', 'First visit'),
(3, 102, 'Mary Smith', 'Mother', '2024-03-02', '11:00:00', 45, 'Completed', 'No issues'),
(4, 103, 'Sarah Johnson', 'Sister', '2024-03-03', '09:30:00', 30, 'Completed', ''),
(5, 104, 'Robert Brown', 'Brother', '2024-03-10', '13:00:00', 60, 'Approved', 'Legal consultation'),
(6, 105, 'Lisa Wilson', 'Wife', '2024-03-05', '10:30:00', 30, 'Completed', ''),
(7, 106, 'Emily Taylor', 'Daughter', '2024-03-08', '15:00:00', 30, 'Denied', 'Not on approved list'),
(8, 107, 'Anna Miller', 'Mother', '2024-03-12', '11:30:00', 45, 'Approved', ''),
(9, 108, 'Olivia White', 'Wife', '2024-03-15', '14:30:00', 30, 'Pending', ''),
(10, 109, 'Sophia Harris', 'Sister', '2024-03-20', '10:00:00', 30, 'Approved', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignment`
--
ALTER TABLE `assignment`
  ADD PRIMARY KEY (`work_ID`),
  ADD KEY `Prisoner_ID` (`Prisoner_ID`);

--
-- Indexes for table `behavior_report`
--
ALTER TABLE `behavior_report`
  ADD PRIMARY KEY (`Report_ID`),
  ADD KEY `Prisoner_ID` (`Prisoner_ID`);

--
-- Indexes for table `cell`
--
ALTER TABLE `cell`
  ADD PRIMARY KEY (`Cell_ID`);

--
-- Indexes for table `complaint`
--
ALTER TABLE `complaint`
  ADD PRIMARY KEY (`Complaint_ID`),
  ADD KEY `Prisoner_ID` (`Prisoner_ID`);

--
-- Indexes for table `medical_record`
--
ALTER TABLE `medical_record`
  ADD PRIMARY KEY (`Medical_ID`),
  ADD KEY `Prisoner_ID` (`Prisoner_ID`);

--
-- Indexes for table `prisoner`
--
ALTER TABLE `prisoner`
  ADD PRIMARY KEY (`Prisoner_ID`),
  ADD KEY `Cell_ID` (`Cell_ID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`Staff_ID`);

--
-- Indexes for table `visitor`
--
ALTER TABLE `visitor`
  ADD PRIMARY KEY (`Visitor_ID`),
  ADD KEY `Prisoner_ID` (`Prisoner_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `prisoner`
--
ALTER TABLE `prisoner`
  MODIFY `Prisoner_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `assignment_ibfk_1` FOREIGN KEY (`Prisoner_ID`) REFERENCES `prisoner` (`Prisoner_ID`);

--
-- Constraints for table `behavior_report`
--
ALTER TABLE `behavior_report`
  ADD CONSTRAINT `behavior_report_ibfk_1` FOREIGN KEY (`Prisoner_ID`) REFERENCES `prisoner` (`Prisoner_ID`);

--
-- Constraints for table `complaint`
--
ALTER TABLE `complaint`
  ADD CONSTRAINT `complaint_ibfk_1` FOREIGN KEY (`Prisoner_ID`) REFERENCES `prisoner` (`Prisoner_ID`);

--
-- Constraints for table `medical_record`
--
ALTER TABLE `medical_record`
  ADD CONSTRAINT `medical_record_ibfk_1` FOREIGN KEY (`Prisoner_ID`) REFERENCES `prisoner` (`Prisoner_ID`);

--
-- Constraints for table `prisoner`
--
ALTER TABLE `prisoner`
  ADD CONSTRAINT `prisoner_ibfk_1` FOREIGN KEY (`Cell_ID`) REFERENCES `cell` (`Cell_ID`);

--
-- Constraints for table `visitor`
--
ALTER TABLE `visitor`
  ADD CONSTRAINT `visitor_ibfk_1` FOREIGN KEY (`Prisoner_ID`) REFERENCES `prisoner` (`Prisoner_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;