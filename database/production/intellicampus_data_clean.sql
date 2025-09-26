--
-- PostgreSQL database dump
--

\restrict 3O9bI7wZdjybjFgT0X3TNFv1cW7BXdnEQaGnovchm2xhgBXeLhTdJ3AIl0Ue1ek

-- Dumped from database version 17.6
-- Dumped by pg_dump version 17.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: academic_calendars; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: academic_period_types; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.academic_period_types VALUES (1, 'Semester', 'SEMESTER', 2, 16, 15, 1, true, NULL, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.academic_period_types VALUES (2, 'Quarter', 'QUARTER', 4, 11, 10, 1, true, NULL, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');


--
-- Data for Name: academic_programs; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.academic_programs VALUES (1, 'BSCS', 'Bachelor of Science in Computer Science', 'bachelor', 'Computer Science', 'Engineering and Technology', 4, 120, 40, 60, 20, 2.00, 2.00, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, '2025-08-24 20:19:37', '2025-08-24 20:19:37', NULL, NULL, NULL, true, 50.00, 'BS', 'undergraduate', 'on-campus', 200, '["freshman","transfer","international"]');
INSERT INTO public.academic_programs VALUES (2, 'BBA', 'Bachelor of Business Administration', 'bachelor', 'Business Administration', 'Business School', 4, 120, 40, 60, 20, 2.00, 2.00, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, '2025-08-24 20:19:38', '2025-08-24 20:19:38', NULL, NULL, NULL, true, 50.00, 'BS', 'undergraduate', 'on-campus', 200, '["freshman","transfer","international"]');
INSERT INTO public.academic_programs VALUES (5, 'BSIT', 'Bachelor of Science in Information Technology', 'bachelor', 'Information Technology', 'Engineering and Technology', 4, 120, 40, 60, 20, 2.00, 2.00, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, '2025-08-25 12:28:45', '2025-08-25 12:28:45', NULL, NULL, NULL, true, 50.00, 'BS', 'undergraduate', 'on-campus', 200, '["freshman","transfer","international"]');
INSERT INTO public.academic_programs VALUES (6, 'BSN', 'Bachelor of Science in Nursing', 'bachelor', 'Nursing', 'Health Sciences', 4, 135, 40, 60, 20, 2.00, 2.00, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, '2025-08-25 12:28:45', '2025-08-25 12:28:45', NULL, NULL, NULL, true, 50.00, 'BS', 'undergraduate', 'on-campus', 200, '["freshman","transfer","international"]');
INSERT INTO public.academic_programs VALUES (13, 'CS', 'Computer Science', 'bachelor', 'Engineering and Technology', 'Faculty of Engineering', 4, 120, 40, 60, 20, 2.00, 2.00, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, '2025-09-25 17:27:24', '2025-09-25 17:27:24', NULL, NULL, NULL, true, 50.00, NULL, 'undergraduate', 'on-campus', NULL, NULL);


--
-- Data for Name: students; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.students VALUES (105, '25000001', 155, 'Test', NULL, 'Student', NULL, 'test.student@example.com', NULL, NULL, NULL, NULL, '2000-01-01', NULL, 'male', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Computer Science', NULL, NULL, NULL, NULL, NULL, 'active', 'good', 'enrolled', NULL, NULL, NULL, NULL, 0.00, 0.00, 0, 0, 120, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, false, false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-09-06 00:39:34', '2025-09-06 00:39:34', '2025-09-06 00:39:34', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (3, '24000003', 56, 'Robert', 'James', 'Johnson', NULL, 'robert.johnson@university.edu', NULL, '+1234567897', NULL, NULL, '2001-11-30', 'Chicago, USA', 'male', 'single', NULL, NULL, 'American', '555666777', '555 University Blvd, New York, NY 10003', '999 Lake Shore Dr, Chicago, IL 60601', 'Engineering', NULL, 'Engineering', 'Mechanical Engineering', NULL, 'sophomore', 'active', 'probation', 'enrolled', '2022-09-01', 2026, NULL, NULL, 1.85, 1.92, 42, 42, 120, 'Chicago Technical High', 2022, 3.20, NULL, NULL, NULL, 'Dr. Michael Brown', 'James Johnson', '+1234567898', 'james.johnson@email.com', 'Sarah Johnson', '+1234567899', 'Sarah Johnson', 'Sister', '+1234567899', 'B+', 'Mild asthma', NULL, NULL, NULL, NULL, true, true, true, true, false, false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, NULL, '2025-08-24 16:54:59', '2025-09-05 23:47:26', NULL, false, false, false, '2027-10-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (11, '24000858', 55, 'Abe', 'Liliane', 'Funk', NULL, 'emmy05@example.org', NULL, '(660) 538-3839', NULL, '+1-380-856-8522', '2002-09-21', 'Paucekland, Madagascar', 'female', 'single', NULL, NULL, 'Azerbaijan', '4244964343', '251 Hahn Springs Suite 807, Aufderharton, WV 41116', '841 Karelle Valleys Apt. 225, Bauchstad, OH 15507', 'Psychology', 1, 'Liberal Arts', 'Psychology', NULL, 'sophomore', 'inactive', 'good', 'enrolled', '2022-04-09', 2028, NULL, NULL, 3.71, 3.66, 31, 31, 120, 'Bailey-Hermiston High School', 2022, 3.53, ' University', NULL, NULL, 'Dr. Ursula Bergstrom IV', 'Dr. Armani Balistreri V', '(763) 791-3328', 'dominique.greenholt@example.net', 'Rebekah Purdy III', '1-223-964-9380', 'Emmy Hackett', 'Father', '+1-629-710-6640', 'B+', NULL, 'Stokes Ltd Insurance', 'POL3380958', '2026-01-28', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2021-02-16', '2020-02-20', '2021-09-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-13 20:18:18', '2025-08-24 16:55:00', '2025-09-05 23:47:25', NULL, true, true, false, '2026-11-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (5, '23000005', 58, 'David', 'Lee', 'Kim', NULL, 'david.kim@university.edu', NULL, '+1234567901', NULL, NULL, '1999-03-15', 'Seoul, South Korea', 'male', 'single', NULL, NULL, 'South Korean', '999888777', '888 Student Hall, New York, NY 10005', NULL, 'Computer Science', NULL, 'Computer Science', 'Artificial Intelligence', 'Data Science', 'graduate', 'graduated', 'good', 'enrolled', '2019-09-01', 2023, '2023-05-15', 'Bachelor of Science in Computer Science', 3.88, 3.85, 120, 120, 120, 'Seoul International School', 2019, 3.92, NULL, NULL, NULL, 'Dr. Chang Liu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'O-', NULL, NULL, NULL, NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, '2025-08-24 16:54:59', '2025-09-05 23:47:26', NULL, false, true, false, '2026-11-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (17, '23006727', 62, 'Benedict', NULL, 'Emard', NULL, 'virginie.lesch@example.net', NULL, '480-537-6815', NULL, NULL, '2002-11-07', 'East Chelseymouth, Brazil', 'male', 'married', 'Judaism', 'Caucasian', 'Costa Rica', '0002488987', '31563 Claudia Summit, Annaborough, AZ 80679', '5015 Stark Heights, New Reinachester, CA 08661', 'Special Education', 1, 'Education', 'Special Education', NULL, 'senior', 'graduated', 'good', 'enrolled', '2020-12-27', 2023, '2024-11-16', 'Bachelor of Science in Special Education', 2.89, 2.68, 93, 93, 120, 'Kutch PLC High School', 2022, 3.69, ' University', NULL, NULL, 'Dr. Hunter Hirthe', 'Mrs. Bonita Batz', '337-545-6534', 'imayert@example.com', 'Leola Crooks', '+1.618.384.7376', 'Valentine Gibson', 'Father', '+15029526654', 'B-', 'Est voluptatem aut eum commodi corporis autem delectus aut.', 'West-Kling Insurance', 'POL5917324', '2026-04-18', NULL, true, true, true, true, true, true, 'BB1832897', 'J-1', '2027-04-05', '2023-03-21', '2022-06-04', '2025-02-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-07-31 06:18:51', '2025-08-24 16:55:00', '2025-09-05 23:47:27', NULL, true, true, false, '2027-11-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (16, '24008569', 76, 'Candace', 'Kane', 'D''Amore', NULL, 'junior73@example.com', NULL, '+1-929-417-2928', NULL, NULL, '1999-06-17', 'Douglasfort, Saint Helena', 'male', 'single', 'Christianity', 'Other', 'Antarctica (the territory South of 60 deg S)', '0903332099', '494 Obie Plains Apt. 354, East Zachariah, MT 54885', '3684 Reichel Bridge, Port Myron, SC 51627', 'English', 1, 'Liberal Arts', 'English', NULL, 'junior', 'active', 'good', 'enrolled', '2024-12-15', 2024, NULL, NULL, 2.44, 2.28, 66, 66, 120, 'Rippin and Sons High School', 2022, 3.61, ' University', NULL, NULL, 'Dr. Mrs. Eden Wilderman', 'Prof. John Champlin', '724-825-8281', 'ethyl85@example.net', 'Donnie Balistreri', '(430) 421-3213', 'Major Gibson', 'Sibling', '+1.458.967.0692', 'AB-', NULL, 'Crooks-Schamberger Insurance', 'POL0568998', '2025-10-12', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-07-12', '2022-10-22', '2022-03-31', '2025-04-04', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-04 04:45:12', '2025-08-24 16:55:00', '2025-09-05 23:47:31', NULL, true, false, false, '2028-01-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (24, '24001441', 63, 'Magali', NULL, 'Barton', NULL, 'colt.lindgren@example.net', NULL, '1-248-277-6508', '(914) 529-1417', NULL, '1995-12-08', 'Ricemouth, Namibia', 'male', 'married', 'Hinduism', NULL, 'Gibraltar', '2418106956', '614 Borer Mills, North Gladys, NV 24060', '33043 Romaguera Groves Apt. 304, New Sharonton, CA 29675', 'Finance', 1, 'Business', 'Finance', NULL, 'graduate', 'active', 'good', 'enrolled', '2021-05-03', 2027, NULL, NULL, 3.55, 3.79, 47, 47, 120, 'Kerluke, Schuster and Hermann High School', 2023, 2.81, 'Lang-Stanton University', NULL, NULL, 'Dr. Jack Wisozk', 'Matilde Ritchie IV', '+1-559-205-4452', 'timmothy.wilderman@example.org', 'Oma Legros', '231-313-3439', 'Dr. Amie Quigley', 'Guardian', '(760) 698-7456', 'A-', NULL, 'Crist-Moore Insurance', 'POL3478561', '2025-11-26', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2020-04-08', '2021-10-10', '2021-07-07', '2025-07-31', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-30 15:36:52', '2025-08-24 16:55:00', '2025-09-05 23:47:27', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (21, '24000107', 80, 'Maximus', 'Rita', 'Lueilwitz', NULL, 'athompson@example.net', 'joy00@example.net', '+1-539-884-3247', '+1 (757) 812-4413', NULL, '2006-03-05', 'North Alexandroport, Sudan', 'male', 'single', 'Buddhism', NULL, 'Nepal', '6694804976', '8452 White Rapids, Port Nathanaelside, MA 66163-9425', '696 Jorge Knoll Suite 721, South Americo, IN 23941', 'Psychology', 1, 'Liberal Arts', 'Psychology', NULL, 'graduate', 'active', 'good', 'enrolled', '2022-08-01', 2025, NULL, NULL, 3.36, 3.26, 30, 30, 120, 'Kunde Group High School', 2021, 2.78, ' University', NULL, NULL, 'Dr. Mrs. Yazmin Dach', 'Sandrine Mueller PhD', '+1-276-601-8165', 'lgerlach@example.com', 'Brown Lehner', '+1-458-747-6651', 'Grady White', 'Mother', '731-622-6193', 'AB+', 'Qui quaerat ut debitis possimus ipsam quia ducimus.', 'Crooks, Rohan and Moen Insurance', 'POL1104621', NULL, NULL, true, true, true, true, true, true, 'BV0895439', 'M-1', '2026-03-15', '2021-04-03', '2020-12-30', '2023-12-31', '2025-08-03', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-02 23:02:47', '2025-08-24 16:55:00', '2025-09-05 23:47:32', NULL, true, false, false, '2028-07-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (22, '24002066', 81, 'Jaleel', NULL, 'Jones', 'Joshuah', 'alexane45@example.com', NULL, '+1-631-984-8907', NULL, NULL, '2005-04-01', 'Morarland, Western Sahara', 'male', 'single', 'Other', 'Caucasian', 'Mongolia', '1395758175', '8332 Stark Plains Suite 844, North Malachichester, NH 84502-8094', '71624 Verda Crest Suite 659, Hoseastad, WA 91029', 'Mechanical Engineering', 1, 'Engineering', 'Mechanical Engineering', 'Mathematics', 'senior', 'active', 'good', 'enrolled', '2024-10-23', 2025, NULL, NULL, 2.90, 3.17, 114, 114, 120, 'Rippin, Davis and Powlowski High School', 2022, 3.40, ' University', NULL, NULL, 'Dr. Mckenna Fay', 'Laurel Larkin DVM', '+17859188461', 'jbraun@example.net', 'Rosa Jakubowski', '+1-872-339-6423', 'Marjory Little Sr.', 'Guardian', '+19125391556', 'B+', NULL, 'Bechtelar-Luettgen Insurance', NULL, '2026-06-04', NULL, true, true, true, true, false, false, NULL, NULL, NULL, '2020-08-09', '2019-10-28', '2021-01-26', '2025-04-10', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-17 09:40:38', '2025-08-24 16:55:00', '2025-09-05 23:47:32', NULL, false, false, false, '2027-01-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (32, '24008237', 90, 'Laura', 'Pat', 'Altenwerth', NULL, 'gina50@example.net', NULL, '936.304.3019', '1-520-944-4535', NULL, '1996-11-16', 'North Willamouth, Indonesia', 'male', 'single', 'Hinduism', NULL, 'China', '7560450004', '377 Joan Flats, Lake Callie, GA 73201-3937', '638 Lemke Ville Suite 223, South Ida, NE 22245', 'Philosophy', 1, 'Liberal Arts', 'Philosophy', NULL, 'graduate', 'active', 'good', 'enrolled', '2023-02-23', 2028, NULL, NULL, 2.70, 2.97, 44, 44, 120, 'Carroll, Kihn and Klein High School', 2021, 3.89, 'Jacobson, Davis and Haley University', NULL, NULL, 'Dr. Miss Marlene Collins', 'Carol Powlowski', '828.625.3233', 'grant.abby@example.com', 'Gerry Abbott', '530.977.2302', 'Felicia Koch', 'Sibling', '(678) 947-8043', 'AB+', NULL, 'Wunsch-Waelchi Insurance', 'POL6925301', NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2020-04-23', '2020-11-07', '2020-10-20', '2025-05-29', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-01 11:52:58', '2025-08-24 16:55:00', '2025-09-05 23:47:34', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (34, '24004219', 92, 'Elvis', 'Jeanie', 'Gusikowski', NULL, 'keon58@example.net', 'nicolas39@example.net', '(878) 757-5060', NULL, NULL, '1997-04-15', 'Bayerport, Liberia', 'male', 'single', NULL, NULL, 'Canada', '1348599011', '61655 Eloise Field Suite 809, East Lavinia, WI 73285', '810 Rath Avenue Suite 008, West Dejah, NY 68902', 'Elementary Education', 1, 'Education', 'Elementary Education', NULL, 'freshman', 'active', 'good', 'enrolled', '2021-09-06', 2025, NULL, NULL, 3.44, 3.53, 11, 11, 120, 'Klocko and Sons High School', 2020, 3.27, 'Hauck and Sons University', NULL, NULL, 'Dr. Mr. Dewayne Kerluke IV', 'Makenna Bruen', '1-949-949-1914', 'demond.emard@example.com', 'Jessica Frami', '401.657.7572', 'Al O''Connell', 'Parent', '1-484-581-9196', 'B+', NULL, 'Runte, Douglas and Brakus Insurance', 'POL8518520', '2026-12-21', NULL, true, true, true, true, true, true, 'UJ4050543', 'J-1', '2026-12-13', '2024-05-25', '2020-07-19', '2024-04-06', '2025-02-25', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-13 14:05:46', '2025-08-24 16:55:00', '2025-09-05 23:47:35', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (44, '23000156', 100, 'Kurtis', NULL, 'McCullough', NULL, 'melany.graham@example.net', NULL, '804-534-9329', NULL, '+12059259205', '1997-05-13', 'Murphyhaven, American Samoa', 'male', 'single', NULL, 'Other', 'Venezuela', '8106658493', '700 Guadalupe Stravenue, East Kavonstad, OK 63578-6089', '744 Krista Coves Apt. 828, North Florianburgh, FL 53415-6486', 'History', 1, 'Liberal Arts', 'History', 'Economics', 'junior', 'graduated', 'good', 'enrolled', '2022-11-03', 2023, '2024-10-12', 'Bachelor of Science in History', 3.06, 3.25, 68, 68, 120, 'Donnelly, Brekke and Gusikowski High School', 2021, 3.70, ' University', NULL, NULL, 'Dr. Garret King', 'Glennie Dickinson', '920-401-9841', 'rempel.geraldine@example.net', 'Regan Yost', '1-775-240-5878', 'Mr. Austin Breitenberg', 'Sibling', '316.634.6096', 'A+', NULL, 'Jakubowski PLC Insurance', 'POL7138099', '2026-09-04', NULL, true, true, true, true, true, true, 'XH9965007', 'M-1', '2029-03-21', '2024-11-30', '2022-02-13', '2021-05-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-07-30 02:32:24', '2025-08-24 16:55:00', '2025-09-05 23:47:37', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (53, '23005607', 108, 'Syble', NULL, 'Fahey', NULL, 'shaina.auer@example.com', NULL, '+1-856-552-0639', '407.355.8542', NULL, '2003-11-26', 'New Hipolito, Kiribati', 'male', 'single', 'Islam', NULL, 'Albania', '4789850254', '7327 Balistreri Port Suite 582, Lemkeshire, AL 18571-9710', '182 Turner Orchard, Vonport, WI 40330', 'Chemical Engineering', 1, 'Engineering', 'Chemical Engineering', NULL, 'junior', 'graduated', 'good', 'enrolled', '2024-11-18', 2023, '2025-05-06', 'Bachelor of Science in Chemical Engineering', 2.54, 2.82, 80, 80, 120, 'Braun-Abernathy High School', 2023, 3.70, ' University', NULL, NULL, 'Dr. Dixie Hayes', 'Talon Simonis', '480-892-9239', 'mosciski.frederik@example.net', 'Layne Block', '(971) 427-8858', 'Porter Bergstrom', 'Mother', '815-504-1584', 'A-', NULL, 'Hackett PLC Insurance', 'POL7641520', '2026-06-30', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2020-04-19', '2024-02-24', '2024-07-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-09 22:58:36', '2025-08-24 16:55:00', '2025-09-05 23:47:39', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (55, '24008954', 109, 'Cydney', 'Birdie', 'Carter', 'Abner', 'rowland16@example.com', 'constantin61@example.org', '331-743-3441', NULL, NULL, '2005-06-21', 'New Juanamouth, Hong Kong', 'male', 'single', NULL, 'Other', 'Brazil', '1919955846', '35806 Paucek Mountains Apt. 923, Lake Cleoland, FL 27645', '9030 Smitham Fields Apt. 562, East Tristinland, DC 58605-1742', 'Cybersecurity', 1, 'Computer Science', 'Cybersecurity', 'Languages', 'sophomore', 'active', 'good', 'enrolled', '2024-10-25', 2026, NULL, NULL, 2.50, 2.76, 55, 55, 120, 'Fay and Sons High School', 2023, 3.74, ' University', NULL, NULL, 'Dr. Leila Gusikowski', 'Orrin Altenwerth', '480.667.2555', 'agoyette@example.org', 'Dejah Skiles', '(747) 324-6674', 'Sincere Senger MD', 'Parent', '1-415-768-4721', 'B-', NULL, 'Conroy-Herzog Insurance', 'POL2492794', '2026-03-19', NULL, true, true, true, true, false, false, NULL, NULL, NULL, '2019-12-27', '2020-04-23', '2022-05-28', '2025-07-05', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-30 17:38:09', '2025-08-24 16:55:00', '2025-09-05 23:47:39', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (56, '23000353', 110, 'Bruce', 'Guiseppe', 'Renner', NULL, 'carter.houston@example.org', NULL, '1-251-615-7723', '(475) 383-7885', NULL, '1999-07-12', 'Bodeshire, Equatorial Guinea', 'male', 'single', 'Judaism', NULL, 'Algeria', '1009120260', '9199 Olga Lakes, Grantborough, NV 83688-5278', '9157 Hoeger Rest, Lake Kyleshire, VA 50115', 'Law', 1, 'Law', 'Law', NULL, 'graduate', 'graduated', 'good', 'enrolled', '2021-01-23', 2023, '2025-04-08', 'Bachelor of Science in Law', 2.05, 2.01, 42, 42, 120, 'Robel LLC High School', 2021, 3.44, ' University', NULL, NULL, 'Dr. Prof. Ruby Beatty II', 'Prof. Sallie Haley', '219.875.3781', 'maurice44@example.net', 'Nicola Zieme', '1-959-437-9428', 'Hunter Hilpert', 'Parent', '678-991-5017', 'AB+', 'Dolor quis quam soluta consequatur numquam.', 'Gibson-Zulauf Insurance', 'POL2641299', NULL, NULL, true, true, true, true, true, true, 'FU3508191', 'J-1', '2027-01-11', '2021-07-19', '2020-06-22', '2024-10-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-11 03:39:50', '2025-08-24 16:55:00', '2025-09-05 23:47:40', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (58, '24008481', 112, 'Alfred', 'Guiseppe', 'Jerde', NULL, 'xhoppe@example.org', 'lois21@example.com', '610.486.1325', '857-299-2222', NULL, '2006-01-05', 'Karlborough, Denmark', 'male', 'single', 'Christianity', NULL, 'Mali', '2085372654', '584 Maxie Haven, Ethelview, GA 64035', '36396 Stamm Point Suite 158, New Mollieburgh, MT 85943-7000', 'Pharmacy', 1, 'Medical Sciences', 'Pharmacy', NULL, 'graduate', 'active', 'good', 'enrolled', '2022-07-03', 2024, NULL, NULL, 2.47, 2.64, 53, 53, 120, 'Raynor-Breitenberg High School', 2021, 2.76, ' University', NULL, NULL, 'Dr. Lenora Hane', 'Terrence Hamill', '1-352-859-6855', 'zmedhurst@example.org', 'Helene Medhurst', '773.332.4974', 'Reynold Bailey', 'Parent', '+1-213-724-2704', 'A-', NULL, ' Insurance', 'POL5783178', '2027-01-07', NULL, true, true, true, true, true, true, 'JH1826798', 'J-1', '2029-03-20', '2023-01-18', '2023-09-26', '2024-01-24', '2025-04-14', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-10 19:53:08', '2025-08-24 16:55:00', '2025-09-05 23:47:40', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (62, '24000943', 116, 'Maeve', 'Madge', 'Thiel', NULL, 'maximillian.miller@example.net', NULL, '+1-646-985-6119', '743-857-9064', NULL, '2001-01-04', 'New Trever, Belarus', 'male', 'single', 'Buddhism', 'Mixed', 'Sweden', '5005309269', '69549 Hegmann Circles, Hyattmouth, AR 10541', '2769 Aileen Lock, Marianoshire, KY 82979', 'Mechanical Engineering', 1, 'Engineering', 'Mechanical Engineering', NULL, 'senior', 'inactive', 'good', 'enrolled', '2023-01-14', 2024, NULL, NULL, 3.99, 3.96, 105, 105, 120, 'Jenkins Inc High School', 2019, 3.46, ' University', NULL, NULL, 'Dr. Raul Bashirian', 'Peggie Stiedemann', '+1-360-520-2636', 'jolie20@example.com', 'Caterina Wyman', '+1.678.899.6770', 'Alessandra McKenzie IV', 'Mother', '1-301-283-8392', 'O-', NULL, 'O''Reilly-Lemke Insurance', NULL, NULL, NULL, false, true, true, true, true, false, NULL, NULL, NULL, '2021-06-23', '2019-12-10', '2024-06-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-13 20:01:18', '2025-08-24 16:55:00', '2025-09-05 23:47:41', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (75, '24004813', 129, 'Cornelius', 'Icie', 'Heidenreich', NULL, 'jevon.quitzon@example.com', NULL, '223.827.5140', NULL, NULL, '1996-08-24', 'South Dougbury, Iran', 'male', 'single', 'Judaism', NULL, 'Qatar', '6317787874', '62878 Padberg Oval, Robelmouth, NH 12133', '4456 Roel Motorway Suite 860, Markusburgh, LA 92841', 'History', 1, 'Liberal Arts', 'History', NULL, 'freshman', 'active', 'good', 'enrolled', '2021-04-08', 2024, NULL, NULL, 2.32, 2.58, 21, 21, 120, 'Gutkowski Group High School', 2021, 4.00, ' University', NULL, NULL, 'Dr. Jasper O''Reilly', 'Miss Ora Cormier', '(531) 261-5937', 'margaret77@example.com', 'Lexie Gusikowski', '520-593-5656', 'Rahul Barton', 'Mother', '+1 (540) 624-9951', 'O-', NULL, 'Conn-Lehner Insurance', 'POL3420175', '2026-11-13', NULL, true, false, true, true, true, false, NULL, NULL, NULL, '2024-08-07', '2024-10-23', '2023-03-06', '2025-03-03', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-06 17:05:10', '2025-08-24 16:55:00', '2025-09-05 23:47:45', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (76, '24002781', 130, 'Stewart', 'Helen', 'Paucek', NULL, 'bstehr@example.org', NULL, '1-815-479-7279', NULL, NULL, '2000-07-31', 'Lakinfort, Belize', 'male', 'single', 'Hinduism', 'Hispanic', 'Antarctica (the territory South of 60 deg S)', '1499801581', '65281 Reilly Mills Suite 765, Gloriatown, IL 14536-4672', '8267 Marisa Route, Russelport, FL 40663-7006', 'Nursing', 1, 'Medical Sciences', 'Nursing', NULL, 'junior', 'active', 'good', 'enrolled', '2021-04-16', 2024, NULL, NULL, 3.74, 3.89, 87, 87, 120, 'Kirlin-Gaylord High School', 2018, 3.97, ' University', NULL, NULL, 'Dr. Frank Mante', 'Maynard Kub PhD', '(361) 788-5698', 'kassandra.zieme@example.com', 'Corine Greenholt', '(978) 783-9388', 'Dorian Heidenreich PhD', 'Mother', '+1-442-295-1184', 'O-', 'Aut quidem ut et quas eum.', 'Schoen LLC Insurance', 'POL4086808', '2026-03-30', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2022-03-03', '2022-01-20', '2021-08-18', '2025-06-14', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-01 03:53:32', '2025-08-24 16:55:00', '2025-09-05 23:47:45', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (82, '24005744', 136, 'Birdie', 'Beau', 'Wuckert', NULL, 'kitty.franecki@example.com', NULL, '(317) 353-1810', '(281) 409-0476', NULL, '2005-05-16', 'Langworthburgh, Italy', 'male', 'single', 'Hinduism', NULL, 'Macedonia', '0780215782', '48125 Okuneva Circles Suite 461, Elmiraton, ME 57612-9580', '6156 Lebsack Forges Apt. 824, Lake Lue, MO 29194', 'Special Education', 1, 'Education', 'Special Education', NULL, 'sophomore', 'active', 'good', 'enrolled', '2023-08-10', 2025, NULL, NULL, 3.93, 3.85, 59, 59, 120, 'Brakus, Kreiger and Konopelski High School', 2019, 3.77, ' University', NULL, NULL, 'Dr. Rahsaan Veum', 'Mrs. Sarai Lindgren', '+1.912.217.6204', 'bailey.avery@example.org', 'Ms. Mercedes Upton IV', '+18569254888', 'Cole Altenwerth DVM', 'Mother', '743.833.4498', 'AB+', NULL, 'Maggio-Murazik Insurance', NULL, '2026-03-06', NULL, true, true, true, true, true, true, 'XW3803577', 'M-1', '2029-08-01', '2022-01-24', '2023-06-25', '2020-11-27', '2025-06-27', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-13 11:47:40', '2025-08-24 16:55:00', '2025-09-05 23:47:47', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (86, '23006200', 139, 'Leilani', 'Marianne', 'Russel', 'Edgar', 'ohowell@example.org', 'virgie17@example.com', '(303) 786-2274', NULL, NULL, '1997-11-09', 'O''Connerbury, Panama', 'male', 'single', 'Christianity', 'Mixed', 'Malta', '2300709996', '8950 Kreiger Views Apt. 836, Lake Josephland, DE 53305-5077', '949 Jacobi Dam Suite 133, Herzogbury, MA 53843', 'Secondary Education', 1, 'Education', 'Secondary Education', 'Philosophy', 'senior', 'graduated', 'good', 'enrolled', '2024-05-18', 2023, '2024-09-17', 'Bachelor of Science in Secondary Education', 2.34, 2.23, 109, 109, 120, 'Adams, Miller and Reynolds High School', 2018, 3.52, ' University', NULL, NULL, 'Dr. Mr. Everett Predovic DVM', 'Dr. Taryn Jaskolski IV', '+1 (781) 515-1767', 'robel.virgie@example.net', 'Kaylee Borer DVM', '337-266-5762', 'Prof. Arch Pfeffer MD', 'Guardian', '+1-904-370-5518', 'AB+', NULL, 'Howell Inc Insurance', 'POL7529606', NULL, NULL, false, true, true, true, true, false, NULL, NULL, NULL, '2022-02-18', '2022-05-10', '2020-11-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-11 11:23:26', '2025-08-24 16:55:00', '2025-09-05 23:47:48', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (99, '24005323', 151, 'Eldora', 'Matilda', 'King', NULL, 'xdicki@example.net', NULL, '+1-308-515-8690', '+17253792973', NULL, '2000-10-16', 'New Vergiehaven, Slovakia (Slovak Republic)', 'male', 'single', NULL, NULL, 'Greenland', '1978957902', '372 Aditya Union, New Heather, GA 01917', '478 Swift Knolls Suite 583, New Mathiashaven, CT 38971-7518', 'Marketing', 1, 'Business', 'Marketing', 'Psychology', 'graduate', 'inactive', 'good', 'enrolled', '2022-02-21', 2028, NULL, NULL, 3.23, 3.32, 48, 48, 120, 'Rutherford Ltd High School', 2022, 3.58, ' University', NULL, NULL, 'Dr. Rachael Mraz', 'Bailee Raynor', '+1-772-548-5118', 'frankie33@example.net', 'Ronny Padberg', '+1-848-272-6844', 'Dulce Schimmel', 'Spouse', '+1.585.783.1025', 'AB+', NULL, 'Adams-Lang Insurance', 'POL0679920', '2026-11-13', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-05-24', '2022-02-05', '2025-01-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-01 01:58:26', '2025-08-24 16:55:01', '2025-09-05 23:47:51', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (4, '24000004', 57, 'Maria', 'Isabel', 'Garcia', NULL, 'maria.garcia@university.edu', NULL, '+1234567900', NULL, NULL, '2000-07-22', 'Madrid, Spain', 'female', 'single', NULL, NULL, 'Spanish', '111222333', '777 Campus Way, New York, NY 10004', 'Calle Mayor 123, Madrid, Spain 28001', 'Medicine', NULL, 'Medical Sciences', 'Pre-Medicine', NULL, 'senior', 'active', 'good', 'enrolled', '2020-09-01', 2024, NULL, NULL, 3.95, 3.91, 110, 110, 120, 'Instituto San Isidro', 2020, 4.00, NULL, NULL, NULL, 'Dr. Patricia Williams', 'Carlos Garcia', '+34612345678', 'carlos.garcia@email.com', 'Ana Garcia', '+34612345679', 'Ana Garcia', 'Mother', '+34612345679', 'AB+', NULL, 'International Student Insurance', 'ISI123456', '2024-12-31', NULL, true, true, true, true, true, true, 'ES9876543', 'F-1', '2024-12-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, NULL, '2025-08-24 16:54:59', '2025-09-05 23:47:26', NULL, false, false, false, '2027-05-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (7, '24001543', 59, 'Zakary', 'Ruthie', 'Miller', NULL, 'miller52@example.net', NULL, '+1-743-481-4192', '(386) 335-3189', NULL, '2005-12-24', 'East Nyaborough, Palau', 'female', 'married', 'Other', 'Asian', 'Spain', '9267032153', '67121 Ethelyn Pine, North Raymondview, IL 79976-4468', '3326 Hills Isle, New Aishamouth, RI 26473', 'Biology', 1, 'Medical Sciences', 'Biology', NULL, 'graduate', 'active', 'good', 'enrolled', '2024-12-19', 2027, NULL, NULL, 2.56, 2.30, 35, 35, 120, 'Greenfelder Inc High School', 2019, 2.83, ' University', NULL, NULL, 'Dr. Dr. Isai Hyatt MD', 'Tessie Tromp II', '+1-765-645-9204', 'walter.mia@example.net', 'Mr. Nikko Nienow', '+1 (334) 769-5090', 'Nannie D''Amore', 'Spouse', '628.929.7268', 'B-', NULL, 'Brekke Ltd Insurance', NULL, '2026-04-14', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-04-20', '2022-06-12', '2024-05-31', '2025-07-16', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-10 12:52:47', '2025-08-24 16:55:00', '2025-09-05 23:47:27', NULL, true, false, false, '2028-05-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (2, '24000002', 60, 'Jane', 'Elizabeth', 'Smith', 'Jane', 'jane.smith@university.edu', NULL, '+1234567894', NULL, NULL, '1999-05-20', 'Toronto, Canada', 'female', 'single', NULL, NULL, 'Canadian', '987654321', '789 College Ave, New York, NY 10002', '321 Maple St, Toronto, ON M5V 3A8', 'Business Administration', NULL, 'Business', 'Finance', 'Economics', 'senior', 'active', 'good', 'enrolled', '2020-09-01', 2024, NULL, NULL, 3.92, 3.85, 108, 108, 120, 'Toronto Central High', 2020, 3.95, NULL, NULL, NULL, 'Dr. Emily Johnson', 'Robert Smith', '+1234567895', 'robert.smith@email.com', 'Robert Smith', '+1234567895', 'Mary Smith', 'Mother', '+1234567896', 'A+', NULL, NULL, NULL, NULL, NULL, true, true, true, true, true, true, 'CA1234567', 'F-1', '2025-08-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, NULL, '2025-08-24 16:54:59', '2025-09-05 23:47:27', NULL, true, true, false, '2027-08-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (54, '23004749', 66, 'Oliver', NULL, 'Nolan', 'Vesta', 'ckulas@example.net', NULL, '(727) 570-3186', NULL, NULL, '2006-08-12', 'Skilesfurt, United States Virgin Islands', 'male', 'single', NULL, 'Asian', 'Ghana', '7761562493', '7285 Hackett Shore Suite 004, Josiestad, MI 09713', '742 Lonzo Crossroad Apt. 546, New Mustafa, UT 91424-6064', 'Cybersecurity', 2, 'Computer Science', 'Cybersecurity', 'Mathematics', 'senior', 'graduated', 'good', 'enrolled', '2021-09-27', 2023, '2025-03-25', 'Bachelor of Science in Cybersecurity', 3.77, 3.60, 102, 102, 120, 'Stokes-Wolf High School', 2022, 3.23, ' University', NULL, NULL, 'Dr. Eldred Wisozk', 'Angie Nader', '+1.856.226.4504', 'nikolaus.jordane@example.org', 'Rachel Mraz', '+14798447725', 'Easter Sawayn', 'Parent', '419-967-6212', 'AB-', NULL, ' Insurance', 'POL8959685', NULL, NULL, true, true, true, true, true, true, 'BC9046951', 'F-1', '2028-04-21', '2019-11-16', '2022-09-21', '2022-01-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-06 13:31:51', '2025-08-24 16:55:00', '2025-09-05 23:47:28', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (9, '24004376', 73, 'Jailyn', 'Patrick', 'Grant', NULL, 'sandrine.koss@example.com', NULL, '551.706.3896', NULL, NULL, '2005-04-18', 'Lake Abigailshire, Belarus', 'female', 'married', 'Hinduism', NULL, 'Pitcairn Islands', '7419857775', '634 Smitham Camp Suite 242, Broderickview, TX 58592', '7351 Nicholaus Inlet, Gaylestad, OR 79029', 'Criminal Justice', 1, 'Law', 'Criminal Justice', NULL, 'senior', 'inactive', 'good', 'enrolled', '2023-02-25', 2024, NULL, NULL, 3.97, 4.00, 112, 112, 120, 'Murray, Denesik and O''Conner High School', 2023, 2.74, ' University', NULL, NULL, 'Dr. Prof. Parker Batz I', 'Adrian Nienow', '+1.351.990.1302', 'neoma98@example.org', 'Prof. Abelardo Wolff', '+1 (270) 667-7271', 'Archibald Predovic', 'Parent', '1-731-525-1566', 'O+', NULL, 'Wolf-Bode Insurance', 'POL1735664', '2025-10-28', NULL, true, false, true, true, false, true, 'MX2429601', 'M-1', '2028-07-27', '2019-09-16', '2020-02-09', '2021-11-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-12 12:37:40', '2025-08-24 16:55:00', '2025-09-05 23:47:30', NULL, false, true, false, '2028-08-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (36, '23009760', 93, 'Rosalind', 'Clifford', 'Brown', 'Cleora', 'stokes.heloise@example.net', NULL, '+1-708-612-2636', NULL, NULL, '2005-03-25', 'Clairville, Slovakia (Slovak Republic)', 'female', 'single', 'Buddhism', 'Asian', 'Slovenia', '3977241702', '718 Camron Walk Apt. 049, Terrellmouth, NE 99496-0303', '321 Heathcote Club, Raynortown, AL 50113', 'Pharmacy', 1, 'Medical Sciences', 'Pharmacy', 'Psychology', 'graduate', 'graduated', 'good', 'enrolled', '2022-11-06', 2023, '2025-02-24', 'Bachelor of Science in Pharmacy', 2.59, 2.38, 42, 42, 120, 'Swift-Blick High School', 2018, 3.32, ' University', NULL, NULL, 'Dr. Gay Howe', 'Kimberly Feil', '423-967-8692', 'raoul99@example.com', 'Leilani Stokes Sr.', '828-538-5972', 'Ruby Fisher', 'Spouse', '415.379.9024', 'B-', 'Sed non libero ut ut.', 'Dickens-Marquardt Insurance', NULL, '2026-08-20', NULL, true, true, true, true, true, true, 'QD4420703', 'M-1', '2027-05-29', '2019-09-12', '2020-10-20', '2023-07-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-08 07:16:01', '2025-08-24 16:55:00', '2025-09-05 23:47:35', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (69, '24005731', 123, 'Kamryn', 'Jason', 'Stoltenberg', NULL, 'vickie51@example.net', NULL, '541-685-6923', '+1.972.472.1243', NULL, '1997-10-29', 'New Gay, Liberia', 'female', 'single', NULL, NULL, 'Mali', '3957708114', '28727 Wisoky Parkways, West Rachelleburgh, UT 92973-4563', '7613 Dare Estate, South Isabella, AZ 45903-9249', 'Philosophy', 1, 'Liberal Arts', 'Philosophy', NULL, 'senior', 'active', 'good', 'enrolled', '2024-10-06', 2028, NULL, NULL, 3.89, 3.63, 112, 112, 120, 'Murazik, Torp and Gleichner High School', 2018, 3.12, ' University', NULL, NULL, 'Dr. Dr. Jacey Nicolas PhD', 'Katelin Donnelly', '(469) 876-1260', 'hill.alyce@example.net', 'Prof. Gianni Heathcote Jr.', '+1-806-996-9454', 'Marion O''Connell', 'Spouse', '831-717-3723', 'O-', 'Aut illo dolor dolor in vero.', 'Oberbrunner-Morissette Insurance', 'POL6852171', '2026-02-21', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2024-03-14', '2024-03-23', '2023-08-22', '2025-04-26', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-04 16:13:17', '2025-08-24 16:55:00', '2025-09-05 23:47:43', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (79, '24007188', 133, 'Herta', 'Bill', 'Prohaska', NULL, 'luisa.jaskolski@example.org', NULL, '1-708-380-4192', '+1.229.501.8759', '+1-458-276-7029', '2004-07-04', 'Hildaville, Sweden', 'female', 'married', NULL, NULL, 'New Zealand', '5667997858', '15937 Erich Neck Suite 934, Muellerside, WI 47671-8850', '66963 Marquis Streets, Lake Xzavier, OK 82375-7224', 'Political Science', 1, 'Law', 'Political Science', NULL, 'sophomore', 'active', 'good', 'enrolled', '2022-02-21', 2027, NULL, NULL, 3.00, 2.82, 33, 33, 120, 'Conn Inc High School', 2019, 2.57, ' University', NULL, NULL, 'Dr. Christopher Gusikowski Jr.', 'Queen Rath', '+1-936-854-2342', 'lane52@example.net', 'Mrs. Elvera Little', '423.742.2110', 'Kaycee Leuschke', 'Parent', '1-425-561-2005', 'AB-', NULL, 'Gerlach-Bartoletti Insurance', 'POL9691057', '2027-05-28', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-04-30', '2020-03-23', '2024-08-04', '2025-08-22', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-31 00:17:58', '2025-08-24 16:55:00', '2025-09-05 23:47:46', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (49, '24000872', 65, 'Kade', NULL, 'Hessel', NULL, 'omraz@example.com', 'aschmitt@example.net', '865.462.2464', '+1-918-649-2188', NULL, '2000-12-28', 'Goodwintown, Tunisia', 'female', 'married', 'Christianity', NULL, 'Saint Helena', '1189000641', '348 Carter Forks, Simonisview, NC 45808-3095', '68106 Spencer Stravenue Apt. 185, Sidneyshire, ME 46125', 'Computer Science', 4, 'Computer Science', 'Computer Science', NULL, 'junior', 'active', 'good', 'enrolled', '2022-07-23', 2025, NULL, NULL, 2.65, 2.56, 63, 63, 120, 'Will, Pfannerstill and Shanahan High School', 2018, 3.28, ' University', NULL, NULL, 'Dr. Delphia Wilkinson', 'Mr. Adolphus Schoen PhD', '586-210-8628', 'tiffany40@example.net', 'Valerie Boyle', '+1-276-404-3123', 'Wilma Kihn', 'Father', '1-240-692-8510', 'AB+', NULL, 'Farrell-Waters Insurance', NULL, '2025-11-06', NULL, true, true, true, true, false, false, NULL, NULL, NULL, '2021-08-04', '2021-08-21', '2021-09-14', '2025-07-15', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-26 16:14:39', '2025-08-24 16:55:00', '2025-09-05 23:47:28', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (18, '24005488', 77, 'Leanne', 'Jaida', 'Crist', 'Ray', 'effertz.bernard@example.net', 'wilderman.kaela@example.net', '1-580-674-8467', '+1.908.270.1117', NULL, '1997-03-27', 'Port Reggiemouth, Finland', 'female', 'married', NULL, NULL, 'Nauru', '4803407969', '857 Dooley Hill, Rogahnfurt, AK 16039', '918 Kihn Mountains Suite 716, East Donnell, MI 52852', 'Software Engineering', 1, 'Computer Science', 'Software Engineering', NULL, 'freshman', 'active', 'good', 'enrolled', '2022-06-30', 2024, NULL, NULL, 2.95, 3.00, 12, 12, 120, 'Nitzsche PLC High School', 2018, 2.89, ' University', NULL, NULL, 'Dr. Jay Cronin MD', 'Blanca Barrows', '(571) 643-4677', 'chasity21@example.net', 'Mr. Dean Stracke DVM', '+1 (915) 708-5478', 'Sylvia King III', 'Parent', '+1.678.941.2366', 'AB+', NULL, 'Grady-Hermann Insurance', 'POL9903801', '2027-03-06', NULL, true, true, true, true, true, true, 'HP6641711', 'F-1', '2029-04-30', '2022-12-29', '2025-02-19', '2023-05-20', '2025-04-17', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-07 02:49:17', '2025-08-24 16:55:00', '2025-09-05 23:47:31', NULL, false, true, false, '2027-08-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (26, '24001980', 84, 'Lyric', 'Bette', 'Terry', 'Aida', 'schroeder.richie@example.org', NULL, '+1-859-389-0344', '281-384-4523', NULL, '1999-02-20', 'Pollyborough, Guernsey', 'female', 'single', NULL, NULL, 'Lesotho', '0353767606', '29071 Andres Mall, Farrellbury, CT 90631-0036', '3341 Schultz Mission, South Cary, WV 06288-1331', 'English', 1, 'Liberal Arts', 'English', NULL, 'graduate', 'active', 'good', 'enrolled', '2021-06-23', 2028, NULL, NULL, 3.13, 3.24, 36, 36, 120, 'Howell-Treutel High School', 2020, 3.19, ' University', NULL, NULL, 'Dr. Stanton Pouros', 'Hermina Fritsch', '(850) 913-4919', 'nlubowitz@example.com', 'Celestino Osinski DVM', '(559) 901-9957', 'Cora Corkery', 'Guardian', '+1 (380) 947-7310', 'A+', NULL, 'Conroy-Corwin Insurance', 'POL4791603', '2026-05-07', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2022-02-04', '2021-08-29', '2024-08-13', '2025-04-09', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-30 21:04:14', '2025-08-24 16:55:00', '2025-09-05 23:47:33', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (28, '24007809', 86, 'Micaela', 'Wilber', 'Koss', NULL, 'jon72@example.org', 'gennaro.greenfelder@example.com', '(351) 312-5494', '+18207983336', NULL, '1997-09-28', 'North Nichole, Jersey', 'female', 'married', 'Buddhism', 'Middle Eastern', 'Tajikistan', '2802287434', '799 Emmanuelle Gardens, Port Kiley, SD 70445', '244 Marcel Street, Brainfort, MN 51062-6792', 'English', 1, 'Liberal Arts', 'English', NULL, 'freshman', 'active', 'good', 'enrolled', '2021-04-04', 2024, NULL, NULL, 3.15, 3.29, 5, 5, 120, 'Pfeffer PLC High School', 2021, 3.66, ' University', NULL, NULL, 'Dr. Dr. Heaven Murazik MD', 'Prof. Filomena Wuckert', '870.699.2480', 'selena31@example.com', 'Stacey Lemke', '774-681-9644', 'Daphne Ward DDS', 'Spouse', '208-826-2015', 'B+', 'Sunt nesciunt adipisci magnam dolor adipisci.', 'Pollich Inc Insurance', 'POL7572922', '2027-08-06', NULL, true, true, true, true, true, true, 'CJ3127401', 'F-1', '2027-02-01', '2019-12-12', '2024-02-12', '2024-02-20', '2025-06-11', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-21 16:28:46', '2025-08-24 16:55:00', '2025-09-05 23:47:33', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (30, '24001079', 88, 'Selmer', 'Rowena', 'Murray', NULL, 'lane85@example.net', NULL, '+1.484.665.4829', '1-415-215-7714', NULL, '2003-07-14', 'Port Caleighland, Argentina', 'female', 'single', 'Islam', 'Caucasian', 'British Virgin Islands', '8220594232', '26136 Feil Spurs, North Wyatt, OK 37895-1303', '3145 Padberg Station, Domenicochester, MD 25548', 'English', 1, 'Liberal Arts', 'English', NULL, 'junior', 'active', 'good', 'enrolled', '2021-07-21', 2028, NULL, NULL, 3.91, 3.74, 78, 78, 120, 'Murphy, Bergstrom and Leffler High School', 2023, 3.13, ' University', NULL, NULL, 'Dr. Madyson Boyle', 'Rosie Bernhard', '(540) 513-0108', 'macie.bernhard@example.net', 'Prof. Heloise Zulauf DVM', '+1-304-717-1391', 'Aleen Thompson', 'Parent', '+1-316-359-1579', 'B-', NULL, 'Willms-Ankunding Insurance', 'POL6997224', NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2021-02-09', '2020-10-20', '2022-07-01', '2025-05-07', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-06 05:35:10', '2025-08-24 16:55:00', '2025-09-05 23:47:34', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (37, '24000635', 94, 'Lexi', NULL, 'Swaniawski', NULL, 'ollie.brown@example.net', NULL, '914.899.2858', '(713) 967-5085', NULL, '2005-07-18', 'East Loyal, Tuvalu', 'female', 'single', NULL, 'Mixed', 'Bahrain', '8842236695', '73052 Beer Stream, Jazminfort, WA 33634', '5506 Veum Gateway Suite 404, Olsonmouth, ND 43804', 'Psychology', 1, 'Liberal Arts', 'Psychology', NULL, 'senior', 'active', 'good', 'enrolled', '2024-02-20', 2026, NULL, NULL, 3.34, 3.14, 115, 115, 120, 'Lindgren, Batz and Ziemann High School', 2020, 2.97, ' University', NULL, NULL, 'Dr. Owen Harris', 'Dario Streich', '(479) 379-4530', 'jhowell@example.org', 'June Murazik', '1-651-813-6083', 'Brianne Douglas', 'Father', '+18309019424', 'B+', NULL, 'Klein, Jerde and Bergnaum Insurance', 'POL9388333', '2027-05-07', NULL, false, true, false, true, true, false, NULL, NULL, NULL, '2021-09-18', '2021-10-19', '2024-09-14', '2025-02-27', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-25 22:11:28', '2025-08-24 16:55:00', '2025-09-05 23:47:35', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (38, '24002675', 95, 'Turner', 'Audie', 'Schinner', NULL, 'qzulauf@example.org', 'schiller.berry@example.com', '+1-651-676-6205', '307-343-2250', NULL, '2005-04-20', 'Vivianton, Tunisia', 'female', 'single', NULL, 'Asian', 'Antarctica (the territory South of 60 deg S)', '6585149670', '595 Joseph Haven Apt. 545, North Dougburgh, UT 37891', '170 Prudence Grove, Talonbury, NH 58393', 'English', 1, 'Liberal Arts', 'English', NULL, 'graduate', 'active', 'good', 'enrolled', '2022-03-04', 2026, NULL, NULL, 2.63, 2.84, 38, 38, 120, 'Reichel-Smith High School', 2021, 3.91, ' University', NULL, NULL, 'Dr. Prof. Alejandrin Johnston III', 'Dr. Easter Gutkowski I', '843.304.6813', 'walker.name@example.com', 'Columbus Kuhlman', '469.215.5266', 'Xzavier Beier', 'Father', '(534) 910-0453', 'AB-', NULL, ' Insurance', NULL, NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2021-10-09', '2022-08-16', '2025-02-18', '2025-07-22', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-15 05:10:25', '2025-08-24 16:55:00', '2025-09-05 23:47:36', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (39, '24003882', 96, 'Shyann', 'Rosalinda', 'Toy', NULL, 'skye.ward@example.org', NULL, '(979) 466-2941', NULL, NULL, '2006-10-20', 'Agustinaport, Thailand', 'female', 'married', NULL, NULL, 'Palau', '9077791249', '84670 Cullen Ports, Verdatown, WI 09731-5649', '2542 Heaney Common, Bartolettifort, UT 58780-5307', 'Software Engineering', 1, 'Computer Science', 'Software Engineering', NULL, 'senior', 'active', 'good', 'enrolled', '2025-01-22', 2026, NULL, NULL, 2.93, 2.90, 98, 98, 120, 'Conroy, Schultz and Mosciski High School', 2018, 2.65, ' University', NULL, NULL, 'Dr. Hollie Ziemann', 'Graciela Harber', '(801) 257-6088', 'wziemann@example.com', 'Dr. Kenny Funk DVM', '+12344487500', 'Cierra Blick', 'Spouse', '870-215-0857', 'O-', NULL, 'Hansen-Padberg Insurance', 'POL3942740', '2027-08-19', NULL, false, false, true, true, true, true, 'CO7642410', 'F-1', '2028-09-22', '2022-04-30', '2024-08-24', '2023-06-20', '2025-08-06', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-08 12:08:56', '2025-08-24 16:55:00', '2025-09-05 23:47:36', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (42, '24002714', 98, 'Nicholaus', 'Roxanne', 'Bednar', NULL, 'kirk66@example.com', 'lebsack.reece@example.org', '+1.276.634.7057', NULL, NULL, '2000-12-05', 'Tomville, Niue', 'female', 'single', NULL, NULL, 'Puerto Rico', '6589656088', '13652 Arianna Pass, Nikkoview, NY 30516-9137', '1072 Abbott Mission, North Reva, AR 18852', 'Marketing', 1, 'Business', 'Marketing', NULL, 'graduate', 'active', 'good', 'enrolled', '2024-11-20', 2027, NULL, NULL, 3.35, 3.30, 56, 56, 120, 'Prosacco Group High School', 2022, 2.54, ' University', NULL, NULL, 'Dr. Dr. Bertha Morissette', 'Mr. Wilford McClure', '+19304903000', 'littel.lexi@example.net', 'Kameron Lakin DDS', '+1-386-686-4374', 'Angel Kuhic', 'Spouse', '+1 (206) 957-7583', 'B+', NULL, 'Altenwerth Group Insurance', 'POL9401547', NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-11-09', '2021-06-24', '2021-02-07', '2025-07-07', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-06 19:45:14', '2025-08-24 16:55:00', '2025-09-05 23:47:37', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (57, '23007066', 111, 'Frank', NULL, 'Howe', NULL, 'cassin.yvonne@example.com', NULL, '272-631-4294', NULL, NULL, '2006-03-03', 'Gladysview, United States of America', 'female', 'single', 'Judaism', 'Other', 'Andorra', '3449636548', '718 Gleason Rapid, New Lydia, GA 16901', '556 Kshlerin Brook Apt. 803, Maxiehaven, SC 52806', 'Medicine', 1, 'Medical Sciences', 'Medicine', NULL, 'sophomore', 'graduated', 'good', 'enrolled', '2022-01-24', 2023, '2025-03-20', 'Bachelor of Science in Medicine', 2.34, 2.53, 51, 51, 120, 'Gutkowski Inc High School', 2023, 2.64, ' University', NULL, NULL, 'Dr. Isabell Witting V', 'Kody Kuhn', '1-865-552-5166', 'gjohnston@example.org', 'Jamey Denesik', '+1 (469) 499-7191', 'Kara Altenwerth', 'Spouse', '(270) 425-8115', 'B+', NULL, ' Insurance', 'POL7546005', '2026-02-02', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-09-06', '2021-11-27', '2020-10-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-09 05:59:52', '2025-08-24 16:55:00', '2025-09-05 23:47:40', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (63, '24009995', 117, 'Kiarra', 'Theodore', 'Roob', NULL, 'gerard12@example.org', NULL, '1-940-221-6961', NULL, NULL, '2000-01-01', 'New Carlottachester, Indonesia', 'female', 'married', NULL, NULL, 'Oman', '7376033458', '120 Iliana Oval Apt. 378, Spinkafurt, MA 09801-2775', '647 Kailyn Garden Suite 314, Taniaville, WV 30545-5062', 'Computer Science', 1, 'Computer Science', 'Computer Science', NULL, 'freshman', 'active', 'good', 'enrolled', '2021-04-14', 2028, NULL, NULL, 3.66, 3.40, 19, 19, 120, 'Bernhard-Abshire High School', 2022, 2.61, ' University', NULL, NULL, 'Dr. Mr. Quinn Anderson PhD', 'Teagan Howell', '918.431.4481', 'christina79@example.net', 'Kelton Schaefer', '828-202-8090', 'Mr. Santiago Dickens III', 'Guardian', '843-884-6172', 'O-', NULL, 'Bosco-Halvorson Insurance', NULL, '2025-09-20', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2024-01-07', '2019-10-03', '2020-11-11', '2025-06-01', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-26 02:03:19', '2025-08-24 16:55:00', '2025-09-05 23:47:41', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (64, '24005244', 118, 'Gino', 'Heber', 'Mitchell', NULL, 'brant66@example.net', NULL, '272-575-1255', NULL, NULL, '1997-04-06', 'North Lindsayview, Reunion', 'female', 'single', 'Christianity', NULL, 'Congo', '5769872413', '275 Ray Haven, Port Celia, KY 85457', '764 Lubowitz Prairie Apt. 825, Port Kameronhaven, OH 00006-9935', 'Accounting', 1, 'Business', 'Accounting', NULL, 'freshman', 'inactive', 'good', 'enrolled', '2021-01-07', 2026, NULL, NULL, 3.78, 3.59, 3, 3, 120, 'Zboncak, Bode and Halvorson High School', 2020, 3.30, ' University', NULL, NULL, 'Dr. Trent Wiza', 'Prof. Brayan Runolfsson V', '(283) 944-2718', 'senger.derek@example.net', 'Prof. Linnea Mayert DVM', '+1-276-740-3507', 'Dorthy Smith DDS', 'Sibling', '573.708.5693', 'AB-', 'Aspernatur dolorum enim harum numquam iste non.', 'Schamberger, Welch and Lakin Insurance', 'POL6630648', NULL, NULL, true, true, true, true, true, true, 'NM9363592', 'F-1', '2025-10-09', '2021-12-03', '2021-03-23', '2022-04-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-05 16:21:15', '2025-08-24 16:55:00', '2025-09-05 23:47:42', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (65, '24005128', 119, 'Briana', 'Kennith', 'Leffler', NULL, 'xbrekke@example.net', NULL, '(682) 852-0575', NULL, NULL, '2003-02-17', 'Eddport, Israel', 'female', 'single', NULL, 'African', 'Antigua and Barbuda', '9395893227', '669 Fay Wells Apt. 519, Hubertbury, NH 27658', '404 Lavinia Track Apt. 833, South Jannieport, IL 24896-7813', 'Pharmacy', 1, 'Medical Sciences', 'Pharmacy', NULL, 'senior', 'active', 'good', 'enrolled', '2021-05-20', 2028, NULL, NULL, 3.10, 3.29, 100, 100, 120, 'Barrows, Greenfelder and Gulgowski High School', 2020, 3.34, ' University', NULL, NULL, 'Dr. Miss Marcella Cummings DVM', 'Estrella Sipes Jr.', '+1-445-510-7056', 'zkrajcik@example.net', 'Charlene Schmeler', '+1.626.899.9854', 'Anjali Parker', 'Guardian', '208.335.8633', 'O+', 'Aut eos quaerat soluta ut error aut sapiente.', ' Insurance', 'POL0315488', NULL, NULL, false, true, true, true, true, false, NULL, NULL, NULL, '2022-03-18', '2023-02-11', '2021-08-27', '2025-04-24', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-23 20:33:06', '2025-08-24 16:55:00', '2025-09-05 23:47:42', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (66, '23005866', 120, 'Triston', 'April', 'Botsford', NULL, 'elenora.beatty@example.org', NULL, '838-621-4793', NULL, NULL, '2006-02-09', 'Jeramytown, Uruguay', 'female', 'single', 'Hinduism', NULL, 'Andorra', '6129004214', '439 Pansy Prairie Suite 711, Lake Enosside, HI 26132', '4679 Goldner Manors, North Angelfurt, MN 91400', 'Psychology', 1, 'Liberal Arts', 'Psychology', 'Economics', 'senior', 'graduated', 'good', 'enrolled', '2021-03-24', 2023, '2024-12-31', 'Bachelor of Science in Psychology', 3.90, 3.88, 117, 117, 120, 'Auer Group High School', 2018, 3.25, ' University', NULL, NULL, 'Dr. Mr. Maximo Kirlin V', 'Shana Jakubowski II', '+1.585.320.5080', 'andres72@example.com', 'Summer Lueilwitz', '1-650-253-9817', 'Gladyce Bahringer', 'Spouse', '+1-364-336-6708', 'A-', 'Inventore nostrum at quam voluptatum ex non non.', 'Frami, Deckow and Bradtke Insurance', 'POL0320132', NULL, NULL, true, false, true, true, true, false, NULL, NULL, NULL, '2024-04-21', '2020-04-20', '2024-06-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-23 13:46:00', '2025-08-24 16:55:00', '2025-09-05 23:47:42', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (70, '24000774', 124, 'Agustin', 'Sharon', 'Halvorson', NULL, 'fabernathy@example.net', NULL, '1-304-730-4844', '1-830-322-2011', NULL, '1997-12-13', 'Dibbertburgh, Tajikistan', 'female', 'single', NULL, NULL, 'Myanmar', '0496485491', '8935 Kunze Fort, Keelingmouth, WI 89545-0717', '24382 Jonathan Keys Suite 697, Cassinton, OH 89636-4325', 'Electrical Engineering', 1, 'Engineering', 'Electrical Engineering', NULL, 'freshman', 'active', 'good', 'enrolled', '2024-07-01', 2027, NULL, NULL, 3.49, 3.40, 29, 29, 120, 'Stracke, Kunze and Thiel High School', 2020, 2.92, ' University', NULL, NULL, 'Dr. Angelina Douglas', 'Danielle Huel', '+1-973-202-8549', 'rhiannon.turcotte@example.com', 'Mr. Gerald O''Hara', '845.790.1257', 'Mrs. Billie Powlowski', 'Parent', '737.739.8532', 'O-', NULL, 'Willms Group Insurance', 'POL1292338', '2026-03-28', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2022-06-23', '2023-03-31', '2021-12-30', '2025-08-20', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-29 12:23:26', '2025-08-24 16:55:00', '2025-09-05 23:47:44', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (72, '23002077', 126, 'Agnes', 'Ariane', 'Schinner', 'Triston', 'maybelle.spencer@example.org', NULL, '828.436.8559', '423.785.0642', NULL, '2001-03-28', 'North Hazle, Martinique', 'female', 'single', 'Christianity', NULL, 'Finland', '9413756505', '51500 Farrell Hill Apt. 467, West Brookchester, ID 73016', '6372 Leo Ridge, Lake Alanisshire, RI 01956', 'Law', 1, 'Law', 'Law', NULL, 'graduate', 'graduated', 'good', 'enrolled', '2022-11-15', 2023, '2024-12-15', 'Bachelor of Science in Law', 2.16, 2.03, 36, 36, 120, 'Boyer Ltd High School', 2021, 3.52, ' University', NULL, NULL, 'Dr. Mrs. Yasmeen Streich DDS', 'Prof. Elaina Swaniawski PhD', '+1.972.928.9438', 'patsy90@example.com', 'Jeanette Wilkinson', '475.733.2622', 'Mauricio Denesik', 'Parent', '(443) 317-0262', 'B+', NULL, 'Rohan, Witting and Macejkovic Insurance', 'POL5187036', NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2020-12-20', '2020-06-14', '2022-06-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-17 08:37:08', '2025-08-24 16:55:00', '2025-09-05 23:47:44', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (74, '24000054', 128, 'Lillie', 'Arvel', 'Crona', NULL, 'vinnie.murray@example.com', NULL, '1-303-941-1933', '+1-520-950-7900', NULL, '2006-02-07', 'North Wilfredfurt, Austria', 'female', 'single', NULL, NULL, 'Congo', '6175778448', '4941 Kelli Mill, Krystinatown, DE 13355', '9133 Zieme Avenue Suite 377, Lake Leonel, NE 45325', 'Medicine', 1, 'Medical Sciences', 'Medicine', 'Philosophy', 'graduate', 'inactive', 'good', 'enrolled', '2022-02-02', 2026, NULL, NULL, 2.90, 2.88, 39, 39, 120, 'Hane Inc High School', 2020, 3.94, ' University', NULL, NULL, 'Dr. Dr. Brannon Waters', 'Prof. Dylan Hammes DVM', '+1 (515) 852-5538', 'brett.rohan@example.org', 'Elbert Miller', '1-424-893-0794', 'Chadrick Davis', 'Parent', '(870) 532-6503', 'O+', NULL, 'Dietrich-Kirlin Insurance', 'POL0447249', NULL, NULL, false, true, true, false, false, false, NULL, NULL, NULL, '2021-12-18', '2022-01-02', '2021-12-26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-29 10:02:28', '2025-08-24 16:55:00', '2025-09-05 23:47:45', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (85, '23007878', 138, 'Nina', 'Weston', 'Hirthe', 'Rupert', 'isaiah.hettinger@example.com', 'lauren.emard@example.com', '(929) 807-1692', NULL, NULL, '2007-06-13', 'West Theofurt, Cyprus', 'female', 'single', NULL, NULL, 'Bangladesh', '6217622281', '8321 Lisette Hills Apt. 807, Emmanuelleshire, DC 92578-7630', '415 Myrtis Via Suite 431, Hoseaport, KY 68303-2182', 'Biology', 1, 'Medical Sciences', 'Biology', NULL, 'graduate', 'graduated', 'good', 'enrolled', '2023-02-02', 2023, '2025-04-15', 'Bachelor of Science in Biology', 2.73, 2.48, 58, 58, 120, 'Wisozk-Kohler High School', 2020, 2.83, ' University', NULL, NULL, 'Dr. Dr. Alessandro DuBuque V', 'Prof. Peter Lindgren V', '(405) 241-9443', 'collier.dalton@example.com', 'Hester Turner', '+1 (689) 889-6995', 'Hudson Greenholt', 'Spouse', '(952) 886-1090', 'O+', NULL, 'Wolf, Erdman and Pollich Insurance', 'POL1583395', '2026-09-08', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2019-09-27', '2025-02-13', '2021-05-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-07-30 14:49:55', '2025-08-24 16:55:00', '2025-09-05 23:47:47', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (91, '23009020', 144, 'Jacynthe', 'Adelbert', 'Hoeger', NULL, 'rowena.rippin@example.com', 'ceasar44@example.net', '1-701-435-9326', NULL, '(567) 693-3309', '2005-04-15', 'Eleazarland, Isle of Man', 'female', 'married', NULL, 'African', 'Djibouti', '8510509846', '81674 Vernice Greens Suite 413, Lake Rickiestad, IL 71063-7138', '23382 Weber Lock Apt. 468, West Catharine, MO 92193-3387', 'Civil Engineering', 1, 'Engineering', 'Civil Engineering', 'Languages', 'senior', 'graduated', 'good', 'enrolled', '2023-07-09', 2023, '2025-06-30', 'Bachelor of Science in Civil Engineering', 2.26, 2.33, 98, 98, 120, 'Vandervort, Cummerata and Padberg High School', 2019, 3.31, ' University', NULL, NULL, 'Dr. Dillon Daniel', 'Gino Block', '267-828-2842', 'jaiden94@example.com', 'Darius Fahey', '1-669-269-4920', 'Prof. Werner Rodriguez PhD', 'Father', '+1-223-227-2190', 'AB+', NULL, 'Bogisich LLC Insurance', NULL, NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2022-06-10', '2023-03-21', '2023-12-22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-12 18:41:28', '2025-08-24 16:55:00', '2025-09-05 23:47:49', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (96, '24004082', 148, 'Cecelia', 'Destinee', 'Murazik', NULL, 'turcotte.marcia@example.net', 'mohr.obie@example.net', '714.309.7087', NULL, '+1-707-386-8090', '1998-05-19', 'South Gonzalo, Poland', 'female', 'single', 'Hinduism', NULL, 'Costa Rica', '0773266787', '8760 Murphy Rapid, Lake Willieberg, IN 57295-8708', '211 Devon Lakes, Nathanstad, CO 97728-1663', 'Elementary Education', 1, 'Education', 'Elementary Education', NULL, 'junior', 'active', 'good', 'enrolled', '2024-10-28', 2026, NULL, NULL, 2.74, 2.88, 74, 74, 120, 'Grady, Bruen and Senger High School', 2022, 2.60, ' University', NULL, NULL, 'Dr. Frankie Yundt', 'Hanna Jacobson', '432-260-0399', 'alfred71@example.net', 'Luther Pouros', '(608) 476-5796', 'Maia Dibbert', 'Spouse', '+1 (843) 699-3024', 'B+', NULL, 'Medhurst-Boyle Insurance', NULL, '2025-10-07', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2025-01-08', '2021-06-12', '2023-09-11', '2025-07-04', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-24 01:24:42', '2025-08-24 16:55:01', '2025-09-05 23:47:50', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (97, '23006982', 149, 'Concepcion', NULL, 'Shields', NULL, 'karina76@example.com', NULL, '+1.938.588.3583', NULL, NULL, '2005-03-24', 'Elmiratown, Niue', 'female', 'single', NULL, NULL, 'Tajikistan', '6750313205', '92406 Kelli Plaza, Watsicaberg, MN 40990', '9767 Chloe Rue Apt. 530, West Mckenna, CO 75607', 'Computer Science', 1, 'Computer Science', 'Computer Science', 'Languages', 'sophomore', 'graduated', 'good', 'enrolled', '2023-01-16', 2023, '2024-08-25', 'Bachelor of Science in Computer Science', 2.34, 2.24, 52, 52, 120, 'Frami, Monahan and Murphy High School', 2023, 2.96, ' University', NULL, NULL, 'Dr. Johann DuBuque', 'Jameson Lubowitz', '716.297.4316', 'runolfsson.nicolas@example.org', 'Alan Mayert', '262-656-9577', 'Mr. Jaren Turner', 'Guardian', '1-425-966-4203', 'A+', NULL, 'Auer, Halvorson and Ruecker Insurance', NULL, '2026-10-03', NULL, false, true, true, true, true, false, NULL, NULL, NULL, '2021-05-15', '2023-05-18', '2023-09-22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-07-31 15:47:01', '2025-08-24 16:55:01', '2025-09-05 23:47:50', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (6, '24000450', 61, 'Jared', 'Gaston', 'Bauch', NULL, 'bullrich@example.com', NULL, '920.556.1137', NULL, NULL, '2001-06-17', 'North Danny, Nigeria', 'other', 'single', NULL, 'Asian', 'British Virgin Islands', '3663002467', '927 Rippin Trail Apt. 497, Bartellchester, IN 59135-2110', '1851 Breitenberg Estates, Tremblaybury, MS 92421', 'Marketing', 1, 'Business', 'Marketing', NULL, 'sophomore', 'active', 'good', 'enrolled', '2023-02-22', 2028, NULL, NULL, 3.78, 3.61, 45, 45, 120, 'Block, Nolan and Collier High School', 2022, 3.12, ' University', NULL, NULL, 'Dr. Ralph Kohler', 'Judge Roob', '1-539-632-4929', 'lind.gudrun@example.org', 'Laurel Gutmann', '208-853-5130', 'Allene Heller', 'Guardian', '(352) 219-6575', 'A-', NULL, 'Kilback, Kling and Marks Insurance', NULL, '2027-01-03', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2019-09-19', '2021-12-04', '2023-11-04', '2025-05-18', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-08 15:53:05', '2025-08-24 16:55:00', '2025-09-05 23:47:27', NULL, true, true, false, '2027-06-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (83, '24009379', 68, 'Lelia', 'Graciela', 'Pollich', NULL, 'bessie.kulas@example.com', NULL, '+1-586-277-7696', NULL, NULL, '2003-02-06', 'Rowestad, Niger', 'female', 'single', 'Hinduism', NULL, 'Romania', '0291518712', '1203 Denesik Fork, Port Alec, SC 61174', '86502 Mertz Station, New Tyson, MD 65021-1065', 'Finance', 4, 'Business', 'Finance', 'Philosophy', 'senior', 'inactive', 'good', 'enrolled', '2021-06-17', 2026, NULL, NULL, 3.60, 3.61, 110, 110, 120, 'Stamm PLC High School', 2018, 3.38, ' University', NULL, NULL, 'Dr. Rodrick Schmidt', 'Araceli Sanford', '+1-931-742-7401', 'eichmann.gaylord@example.net', 'Mr. Erik Connelly PhD', '870-418-1781', 'Dr. Buster Feeney', 'Sibling', '+1 (463) 485-9023', 'B+', NULL, 'Kunze-Schumm Insurance', 'POL0786794', NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2019-09-27', '2024-08-01', '2022-09-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-05 14:37:35', '2025-08-24 16:55:00', '2025-09-05 23:47:29', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (25, '23001706', 83, 'Lacy', NULL, 'Senger', NULL, 'krajcik.emilio@example.com', NULL, '1-919-517-3357', NULL, NULL, '1998-01-03', 'Port Justinabury, Niue', 'other', 'single', 'Other', 'Other', 'Wallis and Futuna', '3125578172', '3337 Jeramie Track Suite 486, New Hulda, NV 36985-0181', '45294 Farrell Branch Apt. 488, Lazaroburgh, CA 96493-6370', 'Mechanical Engineering', 1, 'Engineering', 'Mechanical Engineering', NULL, 'junior', 'graduated', 'good', 'enrolled', '2021-12-30', 2023, '2025-03-29', 'Bachelor of Science in Mechanical Engineering', 3.22, 3.03, 84, 84, 120, 'Smith-Rogahn High School', 2022, 2.77, ' University', NULL, NULL, 'Dr. Esther Kuvalis', 'Dashawn Marks', '1-478-603-3253', 'madonna57@example.com', 'Ms. Maureen Denesik Sr.', '+1-951-266-2111', 'Dr. Beau Boyle III', 'Spouse', '+15132711001', 'A+', 'Nihil modi totam amet laborum voluptatem sit molestias facere.', 'Conn-Krajcik Insurance', 'POL9561774', '2027-05-21', NULL, true, true, true, true, false, false, NULL, NULL, NULL, '2023-03-13', '2023-01-22', '2025-02-23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-14 07:56:39', '2025-08-24 16:55:00', '2025-09-05 23:47:32', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (47, '24004166', 103, 'Kianna', NULL, 'Bahringer', NULL, 'uweber@example.net', NULL, '+1-830-957-1363', '+1-651-635-1303', NULL, '2006-04-07', 'New Astrid, United States of America', 'other', 'single', 'Hinduism', NULL, 'Luxembourg', '5447450729', '1841 Orn Mountain, East Antoniaview, ND 88921-8619', '25632 Harber Junction Apt. 322, New Wilhelmine, VT 67597', 'Business Administration', 1, 'Business', 'Business Administration', NULL, 'freshman', 'active', 'good', 'enrolled', '2024-01-31', 2028, NULL, NULL, 2.49, 2.50, 22, 22, 120, 'Pouros, Schmidt and Homenick High School', 2018, 2.97, ' University', NULL, NULL, 'Dr. Celestino Ritchie', 'Prof. Lea Koepp III', '+1-541-926-3553', 'rchamplin@example.org', 'Samara Kovacek', '(954) 595-0339', 'Wilber Ondricka', 'Father', '+1-979-269-4662', 'AB-', NULL, 'Bergstrom, Crist and Vandervort Insurance', 'POL6016732', '2026-07-22', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-04-29', '2022-04-10', '2023-11-24', '2025-05-31', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-09 08:20:59', '2025-08-24 16:55:00', '2025-09-05 23:47:38', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (59, '24005640', 113, 'Amely', 'Otho', 'Zulauf', NULL, 'otho.wunsch@example.com', 'garrison.kuhn@example.org', '+1-870-301-7510', '(847) 258-2630', NULL, '2002-01-03', 'Port Crystalmouth, Lesotho', 'other', 'single', 'Hinduism', NULL, 'Malawi', '2210307558', '3535 Hansen Island Suite 394, Moshemouth, WV 75995', '4015 Rau Port Apt. 666, West Laishaview, WI 72659-2805', 'Philosophy', 1, 'Liberal Arts', 'Philosophy', NULL, 'sophomore', 'active', 'good', 'enrolled', '2023-04-19', 2026, NULL, NULL, 2.63, 2.81, 45, 45, 120, 'Lowe-Lesch High School', 2023, 3.68, ' University', NULL, NULL, 'Dr. Rahul Durgan Jr.', 'Mr. Chris Wiza IV', '+1.702.703.4008', 'evangeline14@example.net', 'Santos Weissnat', '+1-443-538-0024', 'Miss Kristina Gusikowski', 'Father', '+1 (623) 501-3237', 'AB-', NULL, 'Schinner-Senger Insurance', 'POL4118742', '2026-12-24', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2022-05-27', '2024-04-06', '2024-09-04', '2025-07-11', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-06 06:54:25', '2025-08-24 16:55:00', '2025-09-05 23:47:40', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (90, '24004617', 143, 'Janis', 'Eugene', 'Windler', NULL, 'gardner05@example.org', NULL, '(458) 855-1864', NULL, '(539) 280-9161', '2000-05-25', 'West Gabrielle, Mongolia', 'other', 'single', 'Buddhism', 'Middle Eastern', 'Benin', '2769371346', '81442 Santino Crest, Mellieville, ID 61402-0464', '81161 Luis Prairie, Kentonhaven, TX 06556-8834', 'Psychology', 1, 'Liberal Arts', 'Psychology', NULL, 'junior', 'active', 'good', 'enrolled', '2024-06-13', 2026, NULL, NULL, 2.55, 2.78, 68, 68, 120, 'Konopelski, Blanda and Fay High School', 2018, 3.85, ' University', NULL, NULL, 'Dr. Anderson Gulgowski II', 'Delilah Stanton', '+18148386926', 'robbie62@example.net', 'Belle Rutherford', '(616) 585-7898', 'Mr. Tremayne Hauck MD', 'Parent', '607.963.8068', 'A+', 'Maxime maxime quisquam voluptas repellendus aliquid aut ducimus.', 'Goyette-Buckridge Insurance', 'POL0187985', '2026-05-27', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2020-09-13', '2020-02-17', '2022-02-14', '2025-03-15', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-07 20:45:14', '2025-08-24 16:55:00', '2025-09-05 23:47:49', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (41, '24008020', 64, 'Michale', NULL, 'Gerlach', NULL, 'nicole.marvin@example.org', NULL, '657-399-5503', '734-796-1337', NULL, '2000-10-04', 'East Rowena, Dominican Republic', 'other', 'single', NULL, 'Mixed', 'Belarus', '5040896826', '934 Jeffery Place Apt. 114, Karastad, ID 44919-3793', '26472 Angeline Via Suite 473, North Annabelle, MS 22698-2527', 'Biology', 3, 'Medical Sciences', 'Biology', 'Philosophy', 'senior', 'inactive', 'good', 'enrolled', '2023-01-03', 2028, NULL, NULL, 2.03, 2.22, 114, 114, 120, 'Kohler and Sons High School', 2023, 3.72, 'Marks-Windler University', NULL, NULL, 'Dr. Ashleigh Jakubowski', 'Mr. Ronny Crooks V', '+1-323-971-4515', 'maeve.pfeffer@example.com', 'Mona Will', '323.220.9344', 'Clementine Skiles', 'Parent', '1-279-595-2887', 'AB+', 'Aliquid deleniti placeat laudantium impedit.', 'Brekke, Stehr and Weber Insurance', 'POL0360398', '2026-05-15', NULL, false, true, true, true, true, false, NULL, NULL, NULL, '2021-01-19', '2024-03-24', '2024-05-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-03 13:57:05', '2025-08-24 16:55:00', '2025-09-05 23:47:28', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (35, '24008534', 67, 'Amir', NULL, 'Ziemann', 'Lily', 'jarrett.gleason@example.net', NULL, '+1.332.625.3310', NULL, NULL, '1999-04-11', 'Lake Joshuah, Madagascar', 'other', 'single', 'Islam', 'Asian', 'Antigua and Barbuda', '6630667242', '1060 Haag Mission Apt. 197, Schneiderfort, MD 26804', '35109 Wisozk Prairie, North Jarrell, NH 83042-5106', 'Accounting', 1, 'Business', 'Accounting', 'Languages', 'junior', 'active', 'good', 'enrolled', '2024-11-26', 2025, NULL, NULL, 2.93, 3.11, 78, 78, 120, 'Kuhic-Cruickshank High School', 2023, 3.39, ' University', NULL, NULL, 'Dr. Myriam Muller IV', 'Curt Reinger', '847-237-3949', 'tanya06@example.com', 'Rae Durgan', '1-930-986-9225', 'Hubert West', 'Mother', '+1-341-616-1206', 'O+', NULL, 'Willms-Bogisich Insurance', NULL, NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-05-03', '2024-05-27', '2020-11-08', '2025-06-01', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-04 15:22:49', '2025-08-24 16:55:00', '2025-09-05 23:47:28', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (15, '24002290', 75, 'Dianna', NULL, 'Gleason', NULL, 'boyer.agustin@example.org', NULL, '(864) 475-8040', '401-888-0617', '1-206-597-7180', '2006-05-23', 'East Rollin, Bahamas', 'other', 'single', NULL, 'Hispanic', 'Grenada', '4155956695', '205 Verona Trafficway Suite 858, Stephonfort, IA 60188', '27027 O''Conner Harbors Suite 126, Williamsonborough, MA 24523-6267', 'English', 1, 'Liberal Arts', 'English', NULL, 'junior', 'inactive', 'good', 'enrolled', '2022-11-19', 2028, NULL, NULL, 2.24, 2.52, 88, 88, 120, 'Welch-Wintheiser High School', 2022, 3.38, ' University', NULL, NULL, 'Dr. Mr. Brayan Strosin IV', 'Wyatt Zieme', '209-897-6235', 'uweber@example.com', 'Laverna Buckridge', '1-571-857-8242', 'Nathan Nitzsche', 'Spouse', '+1-279-888-3302', 'O-', NULL, 'Reynolds, Emmerich and Corkery Insurance', 'POL2964206', '2027-01-02', NULL, true, true, true, true, false, false, NULL, NULL, NULL, '2024-05-02', '2022-07-16', '2023-05-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-27 22:12:08', '2025-08-24 16:55:00', '2025-09-05 23:47:30', NULL, false, false, false, '2028-07-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (19, '24008406', 78, 'Lesley', 'Ozella', 'Ferry', 'Modesto', 'kling.francisco@example.com', 'cartwright.milton@example.net', '+1-820-850-3987', '+1-262-405-0028', NULL, '1996-01-11', 'Reicherthaven, British Virgin Islands', 'other', 'single', NULL, 'African', 'Wallis and Futuna', '3899453290', '67249 Verla Plain Suite 885, North Titus, MT 05003', '7509 Lelah Plaza, Reingerhaven, GA 45445', 'Secondary Education', 1, 'Education', 'Secondary Education', 'Psychology', 'freshman', 'active', 'good', 'enrolled', '2022-10-06', 2028, NULL, NULL, 3.35, 3.08, 14, 14, 120, 'Oberbrunner LLC High School', 2018, 3.09, ' University', NULL, NULL, 'Dr. Miss Megane Gerhold', 'Lauren Cole Sr.', '+1.612.440.6688', 'bkreiger@example.com', 'Bruce Cole', '209-970-6490', 'Bennett Carroll', 'Parent', '+1-772-235-3712', 'O+', NULL, 'Klein-Schuppe Insurance', NULL, '2025-09-25', NULL, true, true, true, true, true, true, 'YG3705916', 'M-1', '2027-03-05', '2021-11-10', '2022-12-19', '2024-01-21', '2025-05-31', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-05 22:42:54', '2025-08-24 16:55:00', '2025-09-05 23:47:31', NULL, false, false, false, '2027-08-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (92, '24000866', 69, 'Nasir', NULL, 'O''Connell', NULL, 'hills.monroe@example.org', NULL, '518.954.6252', NULL, NULL, '2007-02-01', 'West Crawford, Estonia', 'female', 'married', 'Other', 'Other', 'Greenland', '1094227469', '489 Conroy Court, East Haileeville, GA 93710-1268', '27358 Carlos Skyway Suite 577, Larrytown, ND 59140-9300', 'Electrical Engineering', 4, 'Engineering', 'Electrical Engineering', 'Economics', 'senior', 'active', 'good', 'enrolled', '2021-01-07', 2028, NULL, NULL, 2.35, 2.23, 103, 103, 120, 'Dietrich-Runte High School', 2020, 2.63, ' University', NULL, NULL, 'Dr. Mrs. Trisha Treutel', 'Alejandrin Tremblay', '539.904.6626', 'reanna12@example.com', 'Elvis Ward V', '+1 (860) 707-2145', 'Prof. Annabell Ondricka', 'Father', '1-928-265-8778', 'A+', 'Distinctio nemo itaque velit facilis nam eum reprehenderit.', 'Dare-Moen Insurance', 'POL6481280', '2025-10-26', NULL, true, true, true, true, false, true, 'TR6393760', 'J-1', '2027-05-28', '2020-04-14', '2021-12-28', '2022-08-03', '2025-06-02', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-13 21:50:05', '2025-08-24 16:55:00', '2025-09-05 23:47:29', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (20, '24007708', 79, 'Dallin', 'Tressa', 'Gibson', 'Molly', 'hodkiewicz.susana@example.com', NULL, '1-413-725-0851', '(475) 296-0407', NULL, '2006-05-12', 'West Lourdes, Malaysia', 'other', 'single', NULL, 'Caucasian', 'Suriname', '5653832875', '8131 Natalie Alley Suite 977, Josianeburgh, DE 77462', '19580 Rolfson Tunnel Suite 203, Demariotown, UT 36129-2220', 'Political Science', 1, 'Law', 'Political Science', NULL, 'senior', 'inactive', 'good', 'enrolled', '2021-10-03', 2026, NULL, NULL, 3.36, 3.07, 106, 106, 120, 'Gerhold, Homenick and Metz High School', 2021, 3.50, ' University', NULL, NULL, 'Dr. Idell Osinski', 'Ottis Veum', '+1-352-625-9821', 'fae.zemlak@example.net', 'Leopold Dibbert', '870-475-2809', 'Prof. Otto Frami III', 'Sibling', '240-636-0136', 'A+', NULL, ' Insurance', NULL, '2025-12-03', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2024-07-31', '2024-02-28', '2021-05-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-10 18:01:44', '2025-08-24 16:55:00', '2025-09-05 23:47:31', NULL, false, false, false, '2026-11-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (14, '24005883', 82, 'Queen', 'Michel', 'Eichmann', NULL, 'kasey.predovic@example.net', NULL, '716-752-2716', NULL, NULL, '2007-03-13', 'South Terrenceburgh, Northern Mariana Islands', 'other', 'single', NULL, NULL, 'Hungary', '9601946050', '36878 Brendan Forge, Lizziebury, WY 31437-4273', '883 Zita Ferry Apt. 163, Beaulahfort, DE 16776-5708', 'Mechanical Engineering', 1, 'Engineering', 'Mechanical Engineering', 'Philosophy', 'freshman', 'active', 'good', 'enrolled', '2024-11-06', 2026, NULL, NULL, 2.58, 2.63, 19, 19, 120, 'Marks, Deckow and Willms High School', 2018, 2.67, ' University', NULL, NULL, 'Dr. Dr. Rosamond Bechtelar', 'Brett Ryan', '312-448-0123', 'qhermann@example.net', 'Mrs. Theodora Turcotte', '364-223-5168', 'Sierra Ledner I', 'Spouse', '820-999-9164', 'AB-', 'Vitae reiciendis voluptatibus consequuntur suscipit.', 'Wintheiser, Sporer and Little Insurance', 'POL7433747', NULL, NULL, true, true, true, false, true, false, NULL, NULL, NULL, '2019-10-03', '2021-04-19', '2023-01-09', '2025-04-10', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-27 08:04:16', '2025-08-24 16:55:00', '2025-09-05 23:47:32', NULL, false, true, false, '2026-09-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (27, '24001295', 85, 'Demetrius', 'Ward', 'Paucek', NULL, 'kianna54@example.com', NULL, '+1-313-699-5153', NULL, NULL, '1997-12-04', 'Port Trey, Ukraine', 'other', 'single', 'Other', 'African', 'Bangladesh', '4612583563', '179 Kautzer Extensions, New Josiah, LA 48842-5705', '4269 Schuppe Camp, West Noemychester, NV 39992-4688', 'Law', 1, 'Law', 'Law', 'Psychology', 'graduate', 'active', 'good', 'enrolled', '2024-07-06', 2027, NULL, NULL, 2.58, 2.85, 33, 33, 120, 'Vandervort-McCullough High School', 2022, 2.69, ' University', NULL, NULL, 'Dr. Kendrick Dietrich', 'Ricky Morar', '510-748-5454', 'fgutmann@example.net', 'Alanis Schneider II', '669-342-8653', 'Kristofer Hilpert V', 'Sibling', '+1.412.274.3123', 'A+', NULL, 'Kreiger Ltd Insurance', 'POL2240013', '2027-03-24', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2021-07-09', '2024-07-05', '2023-02-03', '2025-03-06', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-24 14:34:00', '2025-08-24 16:55:00', '2025-09-05 23:47:33', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (29, '24006143', 87, 'Ottilie', NULL, 'Flatley', NULL, 'tiffany37@example.com', NULL, '503-645-2185', NULL, NULL, '2000-10-25', 'New Emmetside, Nicaragua', 'other', 'married', NULL, NULL, 'China', '2937572648', '507 Maida Underpass, New Ciara, OK 60177', '54419 Tom Park, Brianneview, KS 14933', 'Special Education', 1, 'Education', 'Special Education', NULL, 'sophomore', 'active', 'good', 'enrolled', '2022-02-23', 2024, NULL, NULL, 2.99, 3.13, 41, 41, 120, 'Ankunding-Hessel High School', 2022, 2.90, ' University', NULL, NULL, 'Dr. Loy Purdy', 'Adrienne Kulas', '708-353-7750', 'irempel@example.com', 'Prof. Jacinto Yundt', '+1 (859) 755-5192', 'Laverne Connelly', 'Parent', '+1-234-749-0207', 'O+', NULL, 'Von-Hirthe Insurance', NULL, '2026-10-07', NULL, true, true, true, true, true, true, 'FG0680395', 'F-1', '2026-03-01', '2021-04-04', '2023-08-29', '2021-09-25', '2025-05-13', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-06 18:07:11', '2025-08-24 16:55:00', '2025-09-05 23:47:33', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (33, '24001794', 91, 'Edmond', 'Triston', 'Kassulke', 'Jaquelin', 'ward.woodrow@example.com', NULL, '364.614.4369', '+1-856-599-9003', NULL, '2000-04-21', 'South Frederikton, Poland', 'other', 'single', NULL, NULL, 'Romania', '3292055766', '655 Elroy Square Suite 359, Gleichnerborough, OK 62207-3091', '305 Champlin Fort, Binsfort, MI 43289-4074', 'Biology', 1, 'Medical Sciences', 'Biology', NULL, 'sophomore', 'active', 'good', 'enrolled', '2022-12-16', 2027, NULL, NULL, 3.37, 3.67, 39, 39, 120, 'Reilly-Halvorson High School', 2021, 2.95, 'Hilpert-Hintz University', NULL, NULL, 'Dr. Josh Heller', 'Alejandra Hansen II', '828.487.5140', 'rice.jennie@example.net', 'Prof. Raoul Auer PhD', '(667) 518-3974', 'Anais Pollich', 'Spouse', '1-959-260-9547', 'B+', NULL, 'Reilly-Reichel Insurance', 'POL3004580', '2026-03-19', NULL, true, false, true, true, true, true, 'ZT9572042', 'J-1', '2027-02-16', '2021-12-02', '2020-11-13', '2024-11-21', '2025-04-27', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-21 13:28:36', '2025-08-24 16:55:00', '2025-09-05 23:47:35', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (40, '23005359', 97, 'Dorris', 'Velda', 'Kutch', NULL, 'ujakubowski@example.com', NULL, '1-779-846-8275', '(607) 878-7918', NULL, '2003-08-21', 'Port Gabeville, Ireland', 'other', 'single', 'Islam', 'Middle Eastern', 'Vanuatu', '6923098035', '411 Farrell Rue Apt. 845, Camyllechester, VA 84723', '5204 Johnathan Wells, East Cliffordfort, OH 16252', 'Nursing', 1, 'Medical Sciences', 'Nursing', NULL, 'sophomore', 'graduated', 'good', 'enrolled', '2022-08-04', 2023, '2025-06-05', 'Bachelor of Science in Nursing', 3.96, 3.92, 43, 43, 120, 'Smith Ltd High School', 2023, 3.02, ' University', NULL, NULL, 'Dr. Franz VonRueden', 'Alvis Denesik', '1-325-362-0776', 'jrunolfsson@example.org', 'Elroy Schaden', '413-938-9884', 'Duncan Graham', 'Parent', '+16825841243', 'AB-', NULL, 'Durgan, Greenfelder and Daniel Insurance', 'POL9107395', '2027-05-05', NULL, false, true, true, true, true, false, NULL, NULL, NULL, '2019-12-21', '2019-08-29', '2024-09-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-22 15:55:35', '2025-08-24 16:55:00', '2025-09-05 23:47:36', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (45, '24006644', 101, 'Chandler', 'Adriel', 'Hoppe', NULL, 'stevie.oberbrunner@example.org', NULL, '+1-520-946-6576', '+1.630.885.8540', NULL, '2005-06-27', 'Kerluketon, Belgium', 'other', 'single', NULL, 'Hispanic', 'Qatar', '4199083055', '8862 Angeline Club Suite 967, Taliaville, ID 47654-5650', '6851 Greenholt Inlet, Lake Chadrick, OH 92615', 'Medicine', 1, 'Medical Sciences', 'Medicine', 'Languages', 'senior', 'active', 'good', 'enrolled', '2022-05-24', 2027, NULL, NULL, 3.93, 3.90, 99, 99, 120, 'Nikolaus LLC High School', 2023, 3.08, ' University', NULL, NULL, 'Dr. Dorthy Considine', 'Mr. Lonzo Stoltenberg III', '+14248821824', 'darren41@example.net', 'Elyse Walker', '724-380-9047', 'Dr. Patricia Moore', 'Spouse', '+15398845269', 'AB-', 'Aspernatur ipsum placeat facilis vel possimus amet.', 'Bosco Ltd Insurance', NULL, '2025-12-19', NULL, true, true, false, true, true, false, NULL, NULL, NULL, '2022-03-07', '2023-06-27', '2024-08-03', '2025-05-04', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-18 10:05:03', '2025-08-24 16:55:00', '2025-09-05 23:47:37', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (46, '23007065', 102, 'Antwan', 'Judge', 'Hodkiewicz', 'Loy', 'rosa66@example.org', NULL, '+1-231-923-2066', NULL, NULL, '1998-12-08', 'Homenickborough, Vietnam', 'other', 'single', NULL, NULL, 'El Salvador', '3449577507', '3781 Medhurst Orchard Apt. 910, Swaniawskiview, NY 37190-2860', '5154 Dewayne Terrace Apt. 159, Darianastad, MI 38925', 'History', 1, 'Liberal Arts', 'History', NULL, 'senior', 'graduated', 'good', 'enrolled', '2023-12-26', 2023, '2024-12-01', 'Bachelor of Science in History', 2.70, 2.91, 103, 103, 120, 'Sauer, Hoeger and Becker High School', 2023, 2.55, ' University', NULL, NULL, 'Dr. Eda Botsford', 'Dayne Skiles', '(936) 558-7029', 'gzieme@example.net', 'Amelia Breitenberg', '762-980-2567', 'Lourdes McLaughlin', 'Spouse', '360.774.1381', 'O-', NULL, 'Armstrong Group Insurance', 'POL2885347', '2026-02-03', NULL, true, true, true, false, true, false, NULL, NULL, NULL, '2020-10-22', '2020-07-30', '2021-09-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-04 07:34:10', '2025-08-24 16:55:00', '2025-09-05 23:47:38', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (1, '24000001', 70, 'John', 'Michael', 'Doe', 'Johnny', 'john.doe@university.edu', 'johndoe@gmail.com', '+1234567890', '+1234567891', NULL, '2000-01-15', 'New York, USA', 'male', 'single', NULL, NULL, 'American', '123456789', '123 Main St, New York, NY 10001', '456 Home Ave, Boston, MA 02101', 'Computer Science', NULL, 'Computer Science', 'Software Engineering', 'Mathematics', 'junior', 'active', 'good', 'enrolled', '2021-09-01', 2025, NULL, NULL, 3.75, 3.68, 75, 75, 120, 'Lincoln High School', 2021, 3.85, NULL, NULL, NULL, 'Dr. Robert Smith', 'Michael Doe', '+1234567892', 'michael.doe@email.com', 'Jane Doe', '+1234567893', 'Jane Doe', 'Mother', '+1234567893', 'O+', NULL, NULL, NULL, NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, NULL, '2025-08-24 16:54:59', '2025-09-05 23:47:29', NULL, true, true, false, '2027-11-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (8, '24000161', 72, 'Robert', 'Easton', 'Kautzer', NULL, 'gutkowski.mikel@example.org', NULL, '+1 (563) 982-4517', '+1-430-598-5992', '+1 (305) 803-7933', '1996-12-27', 'Heidenreichbury, Algeria', 'male', 'single', 'Other', NULL, 'French Southern Territories', '1416313742', '661 Toni Gateway, Kennediport, MT 44786-3591', '87272 Robel Pass Apt. 729, Susieland, MO 23610-4025', 'Finance', 1, 'Business', 'Finance', NULL, 'junior', 'inactive', 'good', 'enrolled', '2024-04-04', 2027, NULL, NULL, 2.13, 2.07, 89, 89, 120, 'Nolan-Durgan High School', 2020, 2.95, ' University', NULL, NULL, 'Dr. Mrs. Nannie Rowe', 'Dr. Verner Jacobs III', '+1-657-432-4368', 'geovanni05@example.net', 'Ryley Kuhic', '754.872.9579', 'Hosea Gusikowski', 'Mother', '+1.475.625.7002', 'B+', NULL, 'Fisher Ltd Insurance', 'POL6031105', '2026-08-18', NULL, true, true, true, false, true, false, NULL, NULL, NULL, '2022-07-25', '2024-03-05', '2021-04-26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-20 07:32:38', '2025-08-24 16:55:00', '2025-09-05 23:47:30', NULL, false, false, false, '2028-08-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (103, 'STU2024001', 19, 'Test', NULL, 'Student', NULL, 'student@intellicampus.edu', NULL, '555-0001', NULL, NULL, '2000-01-15', NULL, 'male', NULL, NULL, NULL, 'Liberian', NULL, '123 Student Lane, Monrovia', '123 Student Lane, Monrovia', 'Computer Science', 1, NULL, NULL, NULL, 'sophomore', 'active', 'good', 'enrolled', NULL, NULL, NULL, NULL, 3.45, 3.40, 45, 45, 120, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'John Parent', '555-9999', 'parent@example.com', 'Jane Parent', '555-8888', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, false, false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, NULL, '2025-08-25 23:53:09', '2025-08-25 23:53:09', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (13, '24006766', 71, 'Pete', NULL, 'Beier', 'Jack', 'lew22@example.org', NULL, '(352) 319-5030', NULL, NULL, '2001-10-29', 'Myrnaton, Niue', 'other', 'single', 'Islam', 'Hispanic', 'Saint Kitts and Nevis', '0934380552', '79126 Leanne Via, Carloschester, DE 12005-8885', '2075 Norene River Apt. 787, New Alessandra, OR 88460', 'Criminal Justice', 1, 'Law', 'Criminal Justice', NULL, 'graduate', 'active', 'good', 'enrolled', '2021-01-05', 2025, NULL, NULL, 2.23, 2.51, 40, 40, 120, 'Becker-Rohan High School', 2022, 3.70, ' University', NULL, NULL, 'Dr. Serena Weber', 'Richie Ward', '530-715-3799', 'will.meaghan@example.com', 'Alejandra Wuckert', '732-560-5151', 'Brenda Steuber', 'Sibling', '276.573.3537', 'AB-', 'Id illo magni provident vel cupiditate qui.', ' Insurance', NULL, '2027-01-11', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2022-01-28', '2020-03-15', '2022-04-02', '2025-04-26', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-05 22:29:04', '2025-08-24 16:55:00', '2025-09-05 23:47:29', NULL, false, true, false, '2026-12-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (50, '24002640', 105, 'Mittie', NULL, 'Mayer', NULL, 'gordon.bayer@example.net', NULL, '(661) 217-0561', NULL, NULL, '2000-04-18', 'Carolebury, Hong Kong', 'other', 'married', 'Hinduism', 'Middle Eastern', 'Mayotte', '0391603232', '329 Elisha Springs, Fredrickburgh, CT 71262', '2346 Annette Harbor Apt. 219, South Brendon, ID 28443', 'Secondary Education', 1, 'Education', 'Secondary Education', 'Economics', 'junior', 'inactive', 'good', 'enrolled', '2021-02-26', 2028, NULL, NULL, 2.40, 2.26, 62, 62, 120, 'Veum, Cremin and Goodwin High School', 2023, 2.78, ' University', NULL, NULL, 'Dr. Janice Schmitt DDS', 'Ross Rowe', '+1-786-838-8325', 'xgrady@example.com', 'Myrtis Schaden', '1-404-450-0926', 'Edison Keebler', 'Sibling', '+16078198937', 'B+', NULL, ' Insurance', 'POL6334345', NULL, NULL, true, true, true, true, false, true, 'JK3397874', 'J-1', '2026-10-17', '2020-02-18', '2023-02-09', '2023-11-23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-14 09:19:07', '2025-08-24 16:55:00', '2025-09-05 23:47:38', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (51, '24000457', 106, 'Robyn', 'Colt', 'Beatty', 'Gus', 'ipouros@example.com', NULL, '(623) 518-8474', NULL, NULL, '2003-11-09', 'Eunahaven, Vanuatu', 'other', 'single', 'Islam', NULL, 'Fiji', '6812791565', '6360 Karley Village, Hermannfurt, RI 82946', '1062 Herta Squares, Jarenstad, WY 58965', 'Accounting', 1, 'Business', 'Accounting', 'Economics', 'senior', 'active', 'good', 'enrolled', '2023-05-07', 2024, NULL, NULL, 2.85, 2.69, 93, 93, 120, 'Paucek-Brekke High School', 2019, 3.56, ' University', NULL, NULL, 'Dr. Hattie Nolan', 'Clifton Schmeler', '+1-385-971-5371', 'pascale95@example.net', 'Prof. Giles Hill Jr.', '1-480-514-3991', 'Meta Pouros', 'Father', '+1 (215) 735-5663', 'A+', NULL, 'Hudson PLC Insurance', 'POL0915603', '2027-02-23', NULL, true, true, true, true, false, false, NULL, NULL, NULL, '2024-05-05', '2021-09-30', '2024-09-27', '2025-06-24', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-21 10:42:58', '2025-08-24 16:55:00', '2025-09-05 23:47:38', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (52, '24000841', 107, 'Hilario', 'Mekhi', 'Schuppe', 'Jayne', 'dorothea.sporer@example.org', NULL, '+16236960741', '424-425-9667', NULL, '1997-06-25', 'Port Willardton, Antarctica (the territory South of 60 deg S)', 'other', 'married', NULL, NULL, 'Iceland', '8920650456', '35241 Fritsch Hollow Suite 439, West Leopold, PA 48747-7963', '691 Shanon Mission, Lake Kristianfurt, NE 82434-9374', 'Accounting', 1, 'Business', 'Accounting', 'Philosophy', 'graduate', 'inactive', 'good', 'enrolled', '2022-02-15', 2024, NULL, NULL, 2.56, 2.39, 36, 36, 120, 'Rodriguez, Watsica and Gutkowski High School', 2019, 3.62, ' University', NULL, NULL, 'Dr. Miss Ruby Spinka', 'Deanna Emmerich', '(872) 579-8497', 'jrogahn@example.com', 'Geovany Balistreri DDS', '(283) 621-4026', 'Cole Cummerata', 'Sibling', '+1 (541) 627-8679', 'A+', NULL, 'Hodkiewicz-Schoen Insurance', 'POL8250483', '2027-01-21', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2021-05-26', '2020-02-26', '2023-05-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-06 20:32:56', '2025-08-24 16:55:00', '2025-09-05 23:47:39', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (61, '24008821', 115, 'Kory', NULL, 'Murray', NULL, 'randi58@example.org', 'cummerata.ford@example.net', '260-702-2276', '+1-336-668-2072', NULL, '1998-10-31', 'Brakusmouth, Mali', 'other', 'married', 'Other', 'African', 'French Guiana', '3236106308', '9964 Pollich Camp Apt. 263, Miloside, ME 82376-6762', '926 Bogan Lock, Sharonton, VA 68204-6300', 'English', 1, 'Liberal Arts', 'English', NULL, 'junior', 'active', 'good', 'enrolled', '2023-03-19', 2026, NULL, NULL, 3.00, 3.02, 82, 82, 120, 'Schowalter-Doyle High School', 2023, 3.35, ' University', NULL, NULL, 'Dr. Delphia McCullough', 'Mylene Ankunding I', '(931) 646-6518', 'raufderhar@example.org', 'Loren Breitenberg', '310.633.5433', 'Dr. Halie Witting III', 'Mother', '1-231-731-9277', 'B-', NULL, 'Upton, Fritsch and Flatley Insurance', NULL, '2026-12-22', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2022-10-25', '2020-10-07', '2024-04-25', '2025-03-01', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-16 09:23:45', '2025-08-24 16:55:00', '2025-09-05 23:47:41', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (12, '23009911', 74, 'Vanessa', 'Gina', 'Heller', NULL, 'zaria.luettgen@example.org', NULL, '850.372.7998', NULL, NULL, '2001-06-17', 'North Jamarcus, Guinea', 'male', 'married', 'Christianity', NULL, 'Cameroon', '1425406920', '448 McClure Vista Apt. 302, Genovevachester, KY 42754', '2760 Erin Freeway, Prosaccoborough, AK 12341', 'Finance', 1, 'Business', 'Finance', NULL, 'senior', 'graduated', 'good', 'enrolled', '2021-02-12', 2023, '2024-09-27', 'Bachelor of Science in Finance', 3.44, 3.71, 108, 108, 120, 'Larson, Beer and Lowe High School', 2022, 3.11, ' University', NULL, NULL, 'Dr. Billy Kshlerin', 'Mrs. Danyka Okuneva IV', '458-752-8343', 'julius.frami@example.org', 'Prof. Cydney Shields', '1-940-509-9281', 'Miss Amy Schamberger', 'Parent', '(786) 428-3301', 'AB+', 'Velit animi pariatur voluptatem est deleniti.', 'Spencer Group Insurance', 'POL0186060', NULL, NULL, true, true, true, true, false, true, 'HK5519460', 'F-1', '2027-10-26', '2020-11-30', '2019-10-11', '2024-09-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-13 03:35:12', '2025-08-24 16:55:00', '2025-09-05 23:47:30', NULL, false, false, false, '2026-08-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (31, '23004018', 89, 'Eve', 'Nicolas', 'Thiel', NULL, 'brooklyn97@example.net', NULL, '+1.534.975.8206', '+1 (928) 568-5128', NULL, '1999-09-30', 'New Vivianehaven, Kyrgyz Republic', 'male', 'single', 'Christianity', NULL, 'Botswana', '2347832253', '26812 Israel Parkways, Bellshire, CO 71782-6169', '1896 Krajcik Burgs, North Javonbury, NE 09508', 'Biology', 1, 'Medical Sciences', 'Biology', NULL, 'senior', 'graduated', 'good', 'enrolled', '2022-01-31', 2023, '2024-11-17', 'Bachelor of Science in Biology', 3.52, 3.56, 99, 99, 120, 'Eichmann, Dickinson and King High School', 2021, 3.29, ' University', NULL, NULL, 'Dr. America Franecki', 'Mac Barton', '(651) 948-7490', 'nzboncak@example.com', 'Dr. Jaylan Goyette V', '817-643-9470', 'Alfredo Thiel', 'Sibling', '(682) 735-7282', 'AB+', NULL, 'Crooks-Larson Insurance', 'POL1716970', '2026-10-08', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2022-01-12', '2021-08-10', '2024-10-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-05 23:50:13', '2025-08-24 16:55:00', '2025-09-05 23:47:34', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (43, '24003758', 99, 'Greyson', 'Edyth', 'Veum', NULL, 'easter.klocko@example.net', NULL, '(856) 566-3996', '+1.747.650.1578', NULL, '2006-10-10', 'Alessandramouth, Tuvalu', 'male', 'married', 'Other', 'Caucasian', 'Mauritania', '4385843680', '30156 Casper Corners Suite 208, Port Noeliahaven, NE 17690', '3314 Kling Cove Suite 135, West Carleton, CO 63866', 'Cybersecurity', 1, 'Computer Science', 'Cybersecurity', NULL, 'freshman', 'active', 'good', 'enrolled', '2024-04-26', 2027, NULL, NULL, 2.59, 2.89, 16, 16, 120, 'Okuneva and Sons High School', 2020, 2.90, 'Considine PLC University', NULL, NULL, 'Dr. Shirley Lang', 'Aliza Parker', '+1-850-262-6887', 'dolly12@example.org', 'Berneice Leuschke', '+1-930-351-0924', 'Miss Elena Hand', 'Guardian', '+1 (430) 631-0805', 'AB+', NULL, 'Wiza Ltd Insurance', NULL, '2027-03-10', NULL, true, true, true, false, false, false, NULL, NULL, NULL, '2021-07-20', '2021-12-05', '2023-07-04', '2025-07-05', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-16 12:31:18', '2025-08-24 16:55:00', '2025-09-05 23:47:37', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (48, '23009964', 104, 'Audie', 'Rae', 'Schuppe', 'Aisha', 'lang.lamar@example.net', 'dtreutel@example.net', '+1.845.814.3959', '920.379.3261', NULL, '2001-05-20', 'Muellerborough, Burundi', 'male', 'single', NULL, NULL, 'Guinea-Bissau', '2388366005', '1788 Brielle Ridges Suite 297, North Madge, OR 37029-5306', '33623 Denesik Road, Monamouth, IA 98055', 'Chemical Engineering', 1, 'Engineering', 'Chemical Engineering', 'Philosophy', 'graduate', 'graduated', 'good', 'enrolled', '2023-01-23', 2023, '2025-07-14', 'Bachelor of Science in Chemical Engineering', 3.89, 3.82, 38, 38, 120, 'Walsh, Considine and Walker High School', 2022, 3.46, ' University', NULL, NULL, 'Dr. Ms. Ardella Bednar', 'Dr. Brett Terry', '262-870-5140', 'vonrueden.margret@example.net', 'Dr. Lucio Lueilwitz III', '1-820-472-7041', 'Mr. Davonte Vandervort', 'Parent', '+1.830.327.0146', 'AB-', 'Doloribus iusto officia eum ratione eum quas.', 'Jones, Larkin and Kuhlman Insurance', 'POL5942834', '2026-08-01', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-04-15', '2023-02-05', '2024-03-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-16 18:20:02', '2025-08-24 16:55:00', '2025-09-05 23:47:38', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (60, '24003213', 114, 'Anna', 'Aubrey', 'Rutherford', NULL, 'adelia55@example.net', NULL, '+1 (304) 784-1469', '352-831-1563', NULL, '2006-05-03', 'Jerrellstad, Lebanon', 'male', 'single', NULL, NULL, 'San Marino', '4242571916', '3867 Devan Mountains, Halvorsonmouth, UT 42956', '3894 Braulio Tunnel Apt. 590, Markston, MS 80184', 'Psychology', 1, 'Liberal Arts', 'Psychology', 'Psychology', 'senior', 'active', 'good', 'enrolled', '2022-01-04', 2025, NULL, NULL, 2.08, 2.11, 104, 104, 120, 'Schneider LLC High School', 2020, 3.06, 'Ullrich-Kreiger University', NULL, NULL, 'Dr. Adrianna Runolfsdottir', 'Emilie Fadel DVM', '+1 (330) 660-5326', 'otto.bahringer@example.com', 'Lew Towne', '(320) 435-1878', 'Magdalena Breitenberg', 'Mother', '(954) 625-0439', 'O-', NULL, 'Beier, Ziemann and Yost Insurance', 'POL6071909', '2027-04-07', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2024-06-14', '2019-09-27', '2024-02-25', '2025-07-19', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-14 19:09:00', '2025-08-24 16:55:00', '2025-09-05 23:47:41', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (71, '24006828', 125, 'Kennedy', 'Vivienne', 'Marvin', NULL, 'shaun.murphy@example.org', NULL, '+1 (640) 333-9259', '+1-351-737-6546', NULL, '1996-01-05', 'Rosalynfort, Bangladesh', 'male', 'single', 'Other', NULL, 'Guadeloupe', '4281888639', '38248 Myles Ports, East Dudley, HI 42050-8589', '78343 Rosalinda Lock Suite 008, Laurashire, OK 67683', 'Electrical Engineering', 1, 'Engineering', 'Electrical Engineering', 'Mathematics', 'freshman', 'active', 'good', 'enrolled', '2021-07-04', 2026, NULL, NULL, 3.27, 3.54, 23, 23, 120, 'Fay-Zboncak High School', 2021, 3.74, ' University', NULL, NULL, 'Dr. Dr. Lexie McCullough', 'Prof. Ardith Carroll', '667.978.3960', 'qwhite@example.net', 'Mr. Brycen Zboncak Sr.', '+1-229-819-7735', 'Kathleen Stokes', 'Mother', '+15109785991', 'A-', NULL, 'Leuschke and Sons Insurance', 'POL1952575', '2025-11-22', NULL, true, true, true, true, false, false, NULL, NULL, NULL, '2023-09-17', '2023-07-25', '2022-03-23', '2025-05-30', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-06 02:32:29', '2025-08-24 16:55:00', '2025-09-05 23:47:44', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (80, '24003129', 134, 'Hans', 'Rebeka', 'Flatley', 'Jacynthe', 'aleen.mclaughlin@example.com', NULL, '(424) 966-3207', NULL, '+1-878-438-8653', '2006-10-18', 'North Britneymouth, Sao Tome and Principe', 'male', 'single', 'Judaism', 'Asian', 'Jamaica', '9850124024', '63324 Elliot Field Suite 031, Devonberg, MA 78106-0121', '7840 Sanford Unions, East Leilani, KS 40858', 'Mechanical Engineering', 1, 'Engineering', 'Mechanical Engineering', NULL, 'junior', 'inactive', 'good', 'enrolled', '2025-02-06', 2027, NULL, NULL, 3.19, 3.27, 63, 63, 120, 'Pfeffer, Becker and Osinski High School', 2023, 2.55, ' University', NULL, NULL, 'Dr. Johann Wiegand', 'Prof. Oceane Jacobson', '610.939.8680', 'nmayer@example.net', 'Rachelle Schoen V', '+1-463-480-3922', 'Jamaal Hudson Jr.', 'Sibling', '+15153824421', 'AB-', NULL, 'Langosh-Adams Insurance', 'POL9938672', '2026-11-15', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2025-01-07', '2023-12-17', '2024-10-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-06 03:14:16', '2025-08-24 16:55:00', '2025-09-05 23:47:46', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (98, '24001601', 150, 'Jaylin', NULL, 'Lynch', NULL, 'bryana.pollich@example.com', NULL, '949.868.1136', '+1 (636) 500-0726', '+17856365347', '1999-09-05', 'East Marjorie, Cote d''Ivoire', 'male', 'married', 'Judaism', 'Asian', 'Greenland', '5804373630', '4062 Abdullah Plaza, Idellview, AL 02092-8132', '9619 Nikolaus Terrace Suite 441, Reingerbury, MI 93846', 'Special Education', 1, 'Education', 'Special Education', NULL, 'graduate', 'active', 'good', 'enrolled', '2024-08-23', 2026, NULL, NULL, 2.65, 2.49, 48, 48, 120, 'Fay-Fisher High School', 2023, 2.52, ' University', NULL, NULL, 'Dr. Kay Schumm', 'Alyce Marks', '212-539-7634', 'gwintheiser@example.net', 'Anita Durgan III', '+1 (574) 733-8950', 'Rosemarie Wiza', 'Guardian', '+14127756710', 'O-', NULL, 'Runte-Cronin Insurance', NULL, '2026-08-15', NULL, false, true, true, true, false, false, NULL, NULL, NULL, '2024-09-17', '2020-08-16', '2021-12-11', '2025-08-13', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-07 14:25:05', '2025-08-24 16:55:01', '2025-09-05 23:47:50', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (10, '24003330', 153, 'Melvin', 'Lula', 'Orn', NULL, 'isaac.runolfsdottir@example.org', 'pprice@example.com', '515-320-0858', NULL, NULL, '2000-01-07', 'Lake Lorenzamouth, Vietnam', 'female', 'single', NULL, NULL, 'Lao People''s Democratic Republic', '7975198815', '934 Schaefer Estates, East Oswaldside, UT 20304', '91536 Hand Meadow, West Alishaview, AL 68753', 'Secondary Education', 1, 'Education', 'Secondary Education', NULL, 'junior', 'active', 'good', 'enrolled', '2023-10-24', 2024, NULL, NULL, 3.81, 3.52, 88, 88, 120, 'Kuhn, Weissnat and Kuhn High School', 2019, 2.79, ' University', NULL, NULL, 'Dr. Alysa Auer', 'Davonte Renner', '(443) 778-9526', 'dell95@example.net', 'Jevon Leannon', '+17702459472', 'Amara Rath', 'Parent', '(248) 655-6710', 'A-', NULL, 'Dietrich Group Insurance', 'POL0405587', '2025-12-26', NULL, true, false, true, true, true, false, NULL, NULL, NULL, '2021-06-30', '2022-08-24', '2021-12-03', '2025-07-20', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-28 00:13:16', '2025-08-24 16:55:00', '2025-09-05 23:47:51', NULL, false, false, false, '2028-04-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (23, '24005015', 154, 'Elise', 'Sister', 'Nikolaus', NULL, 'white.ambrose@example.com', NULL, '+16314593714', '308-897-8907', NULL, '2001-11-28', 'North Tiffany, Ethiopia', 'female', 'single', NULL, NULL, 'Czech Republic', '7372732457', '7079 Raynor Street, Port Ronaldo, ME 75440', '37347 Schmidt Lake, Sandrineshire, ID 41879-6804', 'Psychology', 1, 'Liberal Arts', 'Psychology', 'Mathematics', 'freshman', 'inactive', 'good', 'enrolled', '2024-05-07', 2027, NULL, NULL, 2.86, 2.75, 28, 28, 120, 'Hagenes, Bayer and Weimann High School', 2023, 3.05, ' University', NULL, NULL, 'Dr. Davion Rosenbaum', 'Louie Von', '+1-864-439-2461', 'dortha.lebsack@example.net', 'Omari Schmitt', '+1-415-237-6718', 'Cecile Rempel PhD', 'Sibling', '614-780-5223', 'B-', NULL, 'Gaylord-Bergnaum Insurance', 'POL9351807', '2026-02-23', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2020-05-04', '2020-04-12', '2022-09-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-28 15:24:48', '2025-08-24 16:55:00', '2025-09-05 23:47:51', NULL, false, false, false, '2027-09-26', NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (67, '23005740', 121, 'Jeramie', 'Joannie', 'Funk', NULL, 'albin.huels@example.org', 'rupert63@example.com', '+16086693283', NULL, NULL, '1999-05-02', 'Rigobertoport, Hungary', 'other', 'single', 'Christianity', NULL, 'Afghanistan', '0016893612', '77671 Roberta Port, South Paulland, AK 95908-9840', '146 Candida Parkways, North Silas, IN 22917-0159', 'Marketing', 1, 'Business', 'Marketing', NULL, 'graduate', 'graduated', 'good', 'enrolled', '2022-05-02', 2023, '2024-10-14', 'Bachelor of Science in Marketing', 2.27, 2.21, 38, 38, 120, 'Mueller, Rosenbaum and Bode High School', 2019, 3.89, ' University', NULL, NULL, 'Dr. Mr. Claud Zieme DVM', 'Devin Abernathy DVM', '+1-828-866-5035', 'swaniawski.lloyd@example.org', 'Devin Treutel', '979.272.4662', 'Dr. Barry Harvey Sr.', 'Guardian', '956.268.6257', 'A+', NULL, 'Auer-Hoppe Insurance', 'POL1054641', NULL, NULL, true, true, true, true, true, true, 'QL9686819', 'M-1', '2028-03-11', '2023-02-16', '2022-11-05', '2020-12-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-07-25 21:04:22', '2025-08-24 16:55:00', '2025-09-05 23:47:43', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (68, '24002487', 122, 'Dejah', NULL, 'Schimmel', NULL, 'damian79@example.net', 'catherine50@example.net', '1-352-878-2906', NULL, NULL, '2006-02-24', 'West Salvatore, Suriname', 'other', 'single', NULL, NULL, 'Fiji', '1886319842', '87911 Zion Forks, Port Salvador, AR 16658', '27630 Morissette Courts Apt. 864, New Fatimamouth, ND 25894', 'Secondary Education', 1, 'Education', 'Secondary Education', 'Philosophy', 'sophomore', 'inactive', 'good', 'enrolled', '2024-07-15', 2028, NULL, NULL, 3.65, 3.56, 41, 41, 120, 'D''Amore Ltd High School', 2020, 2.90, ' University', NULL, NULL, 'Dr. Francisca Runte', 'Mr. Leif Lubowitz', '+1 (762) 592-0668', 'xraynor@example.org', 'Arielle Paucek', '+1 (712) 744-5105', 'Dr. Hermina Hammes', 'Guardian', '908.591.7716', 'O-', NULL, 'Harber-Larkin Insurance', 'POL7117084', NULL, NULL, true, true, false, true, true, false, NULL, NULL, NULL, '2021-10-20', '2023-11-27', '2021-01-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-18 06:01:13', '2025-08-24 16:55:00', '2025-09-05 23:47:43', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (73, '24008015', 127, 'Loyce', 'Janice', 'Pollich', NULL, 'gemard@example.net', NULL, '1-563-872-5732', NULL, NULL, '1996-08-03', 'Hegmannmouth, Mozambique', 'other', 'single', 'Christianity', 'Middle Eastern', 'Mexico', '0549070466', '65680 Jast Canyon Apt. 274, North Hermann, NE 98098-2177', '76217 Skiles Plaza, Whitefurt, OH 01820', 'Law', 1, 'Law', 'Law', 'Psychology', 'sophomore', 'active', 'good', 'enrolled', '2022-09-02', 2028, NULL, NULL, 3.53, 3.53, 37, 37, 120, 'Beatty, Lowe and Lesch High School', 2020, 3.48, ' University', NULL, NULL, 'Dr. Prof. Joanny Wilkinson IV', 'Angelina Jones', '+1-743-566-7348', 'hkunze@example.net', 'Kacie Schuppe', '+1-667-652-5345', 'Jordyn Goodwin', 'Parent', '980-590-1598', 'A-', 'Qui eum dolore soluta dolor quia incidunt.', 'Balistreri-Zieme Insurance', 'POL2872339', '2026-05-31', NULL, true, true, true, true, false, true, 'TL8016252', 'J-1', '2026-09-22', '2023-07-08', '2023-01-06', '2022-06-01', '2025-08-19', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-09 12:44:00', '2025-08-24 16:55:00', '2025-09-05 23:47:44', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (77, '23004782', 131, 'Alyson', 'Westley', 'Jacobs', NULL, 'zweber@example.com', 'arely.doyle@example.net', '507-919-6675', NULL, NULL, '2004-05-08', 'Ethylborough, Christmas Island', 'other', 'single', 'Islam', NULL, 'Bhutan', '2850993723', '113 Rex View, New Aydenton, MO 83780-2041', '39819 Vaughn Crescent Apt. 420, South Eduardofort, RI 83841-5554', 'Chemical Engineering', 1, 'Engineering', 'Chemical Engineering', NULL, 'senior', 'graduated', 'good', 'enrolled', '2024-05-26', 2023, '2024-09-04', 'Bachelor of Science in Chemical Engineering', 3.30, 3.39, 103, 103, 120, 'Dickens-Kuvalis High School', 2023, 3.01, ' University', NULL, NULL, 'Dr. Naomi Hoeger', 'Josephine Lehner', '1-747-700-4259', 'ford70@example.org', 'Walter Gibson II', '+1.248.996.6146', 'Miss Rubie Kautzer PhD', 'Spouse', '1-843-821-6215', 'AB+', 'Consequatur odit commodi exercitationem qui dolorum voluptatibus placeat.', 'Murazik-Wilderman Insurance', 'POL8476037', '2026-12-27', NULL, true, true, true, false, true, false, NULL, NULL, NULL, '2024-02-03', '2023-05-14', '2023-02-21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, '2025-08-13 09:29:27', '2025-08-24 16:55:00', '2025-09-05 23:47:45', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (78, '24007873', 132, 'Shaniya', 'Jeffrey', 'Spinka', NULL, 'jonathan08@example.com', NULL, '303.227.6813', NULL, '+1 (325) 612-9449', '1996-02-02', 'North Selena, Panama', 'other', 'single', NULL, 'Asian', 'Pakistan', '7723118620', '438 Lorenzo Highway Suite 221, Halvorsonchester, AL 16519', '16485 Mraz Mills Apt. 772, Croninbury, KS 33025', 'Cybersecurity', 1, 'Computer Science', 'Cybersecurity', NULL, 'sophomore', 'inactive', 'good', 'enrolled', '2021-05-23', 2025, NULL, NULL, 2.39, 2.44, 36, 36, 120, 'Wiegand-Reichel High School', 2022, 2.55, 'Krajcik LLC University', NULL, NULL, 'Dr. Skye Feest', 'Amelia Morar', '+1.223.903.0966', 'ujacobi@example.com', 'Pansy Rogahn', '585-766-2550', 'Isaias Harber', 'Guardian', '+1.609.507.2692', 'B+', NULL, 'Bednar, Ferry and Upton Insurance', 'POL0234496', NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2022-12-26', '2020-06-06', '2024-07-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-13 06:08:15', '2025-08-24 16:55:00', '2025-09-05 23:47:46', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (81, '24004902', 135, 'Brenda', 'Stuart', 'Wunsch', NULL, 'rau.marquise@example.com', NULL, '+1 (959) 301-7772', '325-346-9023', NULL, '2005-09-14', 'Nikolausbury, Swaziland', 'other', 'single', 'Buddhism', NULL, 'Montenegro', '0608195530', '653 Dickinson Center, Ebbaland, IL 71989-6104', '991 Streich Ville Suite 677, Elinorefort, TX 29538', 'Mechanical Engineering', 1, 'Engineering', 'Mechanical Engineering', NULL, 'senior', 'active', 'good', 'enrolled', '2023-04-29', 2026, NULL, NULL, 3.02, 3.22, 119, 119, 120, 'Willms-Wolff High School', 2018, 2.89, ' University', NULL, NULL, 'Dr. Immanuel Wisoky', 'Kristin Conroy PhD', '(689) 766-4009', 'strosin.dejon@example.net', 'Dr. Raegan Beahan III', '+1-269-692-4446', 'Mr. Murray Hayes MD', 'Parent', '878.835.7545', 'AB+', NULL, 'White, Bayer and Anderson Insurance', 'POL4399654', '2026-01-26', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-11-22', '2021-08-10', '2022-02-08', '2025-05-26', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-25 17:44:52', '2025-08-24 16:55:00', '2025-09-05 23:47:46', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (84, '24007087', 137, 'Coby', 'Faye', 'Schuppe', NULL, 'feest.miles@example.net', NULL, '+1-908-301-7203', '1-910-306-3494', NULL, '1998-01-23', 'Presleyberg, Monaco', 'other', 'single', 'Islam', 'African', 'Mongolia', '0959365818', '173 Shaylee Mill Apt. 975, Terrystad, GA 18948-2099', '803 Cruickshank Mission Apt. 863, West Shainamouth, CA 90144', 'Finance', 1, 'Business', 'Finance', 'Philosophy', 'sophomore', 'active', 'good', 'enrolled', '2024-04-16', 2028, NULL, NULL, 2.13, 2.24, 53, 53, 120, 'Price-Koch High School', 2020, 3.63, ' University', NULL, NULL, 'Dr. Abigayle Corwin', 'Reese Kirlin', '+1-520-848-5297', 'dennis.greenfelder@example.org', 'Tony Connelly', '307.339.2595', 'Mitchel Rolfson', 'Father', '1-872-457-6500', 'AB-', NULL, 'Schaden and Sons Insurance', 'POL7476841', '2027-06-14', NULL, true, true, true, true, true, true, 'SW5690143', 'J-1', '2028-06-18', '2020-07-27', '2021-02-13', '2023-04-23', '2025-08-21', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-21 06:42:54', '2025-08-24 16:55:00', '2025-09-05 23:47:47', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (87, '24006337', 140, 'Marianna', 'Cali', 'Schultz', NULL, 'king.berge@example.net', NULL, '712-519-5732', NULL, NULL, '1995-12-25', 'New Micahshire, Rwanda', 'other', 'single', 'Buddhism', NULL, 'New Zealand', '5121392834', '8479 McClure Mountain, Ziemannmouth, NH 47151', '735 Jamie Well Suite 074, South Tyree, WY 42447-7685', 'Business Administration', 1, 'Business', 'Business Administration', NULL, 'graduate', 'active', 'good', 'enrolled', '2021-11-20', 2025, NULL, NULL, 2.58, 2.79, 56, 56, 120, 'Wyman-Schiller High School', 2020, 3.54, 'Stoltenberg Ltd University', NULL, NULL, 'Dr. Augusta Crooks', 'Caroline Zieme', '754-744-5568', 'edna.medhurst@example.com', 'Claudine Donnelly MD', '+1-773-897-4615', 'Jonathan Stracke', 'Sibling', '+18458876347', 'AB-', 'Debitis iusto error nesciunt.', ' Insurance', NULL, '2027-04-28', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2023-03-27', '2023-03-21', '2024-09-09', '2025-03-05', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-14 16:58:55', '2025-08-24 16:55:00', '2025-09-05 23:47:48', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (88, '24002421', 141, 'Elenor', 'Susanna', 'Klocko', NULL, 'qjerde@example.com', NULL, '(801) 238-5485', NULL, NULL, '2002-05-06', 'Runolfssonburgh, Honduras', 'other', 'single', NULL, NULL, 'Mongolia', '4033836809', '891 Geoffrey Springs Apt. 754, Rutherfordbury, NE 86959-0511', '86771 Sallie Spur Apt. 468, Lake Brendaview, WV 11796', 'Psychology', 1, 'Liberal Arts', 'Psychology', 'Languages', 'freshman', 'inactive', 'good', 'enrolled', '2021-05-09', 2026, NULL, NULL, 3.40, 3.34, 6, 6, 120, 'Walsh, Hilpert and Blick High School', 2022, 3.04, ' University', NULL, NULL, 'Dr. Miss Coralie Kreiger Jr.', 'Deion Langosh', '(743) 457-7740', 'bins.ari@example.net', 'Abagail Greenholt', '+1-283-609-4470', 'Prof. Nikita Waters', 'Sibling', '+1-725-219-4033', 'B+', NULL, 'White, Yost and Schoen Insurance', 'POL2693104', NULL, NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2020-05-25', '2022-01-29', '2022-02-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-27 02:14:17', '2025-08-24 16:55:00', '2025-09-05 23:47:48', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (89, '24002706', 142, 'Madaline', 'Gladyce', 'Pfannerstill', NULL, 'corine79@example.net', NULL, '+1.919.937.6497', NULL, NULL, '2005-02-14', 'New Frida, Bosnia and Herzegovina', 'other', 'single', 'Judaism', 'Middle Eastern', 'Cyprus', '6484672216', '90434 Nader Views, Goldenview, NJ 39283', '7924 Lester Walk Apt. 933, Lake Raheemhaven, IA 32894-7473', 'Software Engineering', 1, 'Computer Science', 'Software Engineering', 'Languages', 'junior', 'active', 'good', 'enrolled', '2022-12-20', 2025, NULL, NULL, 3.83, 3.92, 69, 69, 120, 'Torphy, Wisoky and Crona High School', 2023, 2.71, ' University', NULL, NULL, 'Dr. Grady Reichel DDS', 'Regan Gislason Sr.', '+14255138553', 'jbruen@example.net', 'Corbin O''Reilly', '484.347.9284', 'Pauline Thiel IV', 'Spouse', '+1-870-457-0269', 'O-', NULL, 'Kassulke, O''Hara and Krajcik Insurance', 'POL2446222', NULL, NULL, true, true, true, true, true, true, 'ZH4453072', 'M-1', '2029-05-05', '2023-02-23', '2020-07-30', '2022-10-27', '2025-03-02', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-06 22:33:20', '2025-08-24 16:55:00', '2025-09-05 23:47:48', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (93, '24004695', 145, 'Delta', NULL, 'Ankunding', NULL, 'norberto09@example.net', NULL, '+1-408-609-8257', '1-352-432-1692', NULL, '2001-01-22', 'Kayliville, United States Virgin Islands', 'other', 'single', NULL, 'Caucasian', 'Kyrgyz Republic', '9942954620', '6733 Murray Flat Apt. 027, Rainaville, ME 99344-9562', '20305 Wisoky Lakes, Parisville, DC 52786', 'Pharmacy', 1, 'Medical Sciences', 'Pharmacy', 'Economics', 'senior', 'inactive', 'good', 'enrolled', '2021-01-31', 2025, NULL, NULL, 3.02, 2.93, 95, 95, 120, 'Ferry-Volkman High School', 2022, 3.37, ' University', NULL, NULL, 'Dr. Dr. Claud Strosin', 'Mr. Wilmer Wiegand', '+1-770-299-9704', 'frederique54@example.org', 'Rafaela Smitham', '843.645.4568', 'Jennifer Kuhlman', 'Spouse', '248.393.5364', 'B-', NULL, 'Gerlach, Miller and Stamm Insurance', NULL, NULL, NULL, true, false, true, true, true, false, NULL, NULL, NULL, '2020-01-25', '2019-11-14', '2022-05-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-21 19:53:51', '2025-08-24 16:55:01', '2025-09-05 23:47:49', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (94, '24007427', 146, 'Mozell', 'Reggie', 'Gislason', 'Murphy', 'stanton.ford@example.org', 'mlueilwitz@example.com', '747.671.0424', NULL, NULL, '2004-08-01', 'Houstontown, Croatia', 'other', 'single', 'Other', NULL, 'Falkland Islands (Malvinas)', '0167238320', '83563 Gerhard Pine, Streichview, MO 36131', '2766 Lysanne Extension Apt. 238, Alexanneport, CA 11099', 'Cybersecurity', 1, 'Computer Science', 'Cybersecurity', NULL, 'graduate', 'active', 'good', 'enrolled', '2023-11-07', 2028, NULL, NULL, 3.74, 3.83, 59, 59, 120, 'Abbott-Batz High School', 2022, 2.92, ' University', NULL, NULL, 'Dr. Sim Kemmer', 'Prof. Benedict Russel Sr.', '747.821.7973', 'rogahn.everette@example.net', 'Bonita Swift Sr.', '1-217-620-5785', 'Corrine Prosacco IV', 'Guardian', '+1-786-836-7336', 'A-', NULL, 'Kuhn LLC Insurance', 'POL0952922', '2026-10-22', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2022-08-11', '2023-10-07', '2022-10-23', '2025-03-28', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-22 09:20:18', '2025-08-24 16:55:01', '2025-09-05 23:47:49', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (95, '24007159', 147, 'Casimir', NULL, 'Ondricka', 'Darron', 'ularson@example.com', 'stillman@example.com', '+13852548488', NULL, NULL, '1996-11-28', 'East Imeldabury, Russian Federation', 'other', 'single', NULL, NULL, 'Guernsey', '2530485892', '682 Johnson Prairie, Port Steveport, UT 25741', '71202 Colt Oval, North Jensen, CO 04566', 'Computer Science', 1, 'Computer Science', 'Computer Science', NULL, 'freshman', 'active', 'good', 'enrolled', '2021-02-01', 2026, NULL, NULL, 2.80, 3.00, 1, 1, 120, 'Orn Ltd High School', 2022, 3.19, ' University', NULL, NULL, 'Dr. Mr. Darien Cassin', 'Nikki Considine V', '925.263.6841', 'feil.elnora@example.com', 'Laney Reichel', '+1-586-636-0062', 'Jettie Fisher IV', 'Parent', '231-949-7972', 'AB+', NULL, 'Ferry, Abbott and Wilkinson Insurance', 'POL1281557', NULL, NULL, true, true, true, true, true, true, 'TB7523275', 'J-1', '2027-01-29', '2023-10-15', '2021-04-01', '2023-05-16', '2025-05-12', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-08-22 14:03:31', '2025-08-24 16:55:01', '2025-09-05 23:47:50', NULL, false, false, false, NULL, NULL, NULL, 0, 0);
INSERT INTO public.students VALUES (100, '24008239', 152, 'Chloe', 'Toy', 'Quigley', 'Lucienne', 'brennan94@example.org', 'arno.ernser@example.com', '+1 (352) 569-1727', '(541) 486-5952', NULL, '2006-10-01', 'Xandermouth, Macedonia', 'other', 'single', 'Islam', NULL, 'Iraq', '4006802701', '4992 Cordelia Creek Apt. 144, East Esperanzabury, AL 89319', '349 Hammes Well, New Daniella, NJ 45322', 'Pharmacy', 1, 'Medical Sciences', 'Pharmacy', NULL, 'graduate', 'active', 'good', 'enrolled', '2024-06-08', 2028, NULL, NULL, 3.35, 3.21, 49, 49, 120, 'Jacobs, Considine and Mayer High School', 2020, 3.28, ' University', NULL, NULL, 'Dr. Dr. Giovanna Smitham Jr.', 'Valentine Gibson', '815.954.9285', 'lon.konopelski@example.org', 'Pete Ledner', '+16804010304', 'Billy Bogisich', 'Mother', '775-367-3034', 'B-', NULL, ' Insurance', 'POL9472617', '2025-11-11', NULL, true, true, true, true, true, false, NULL, NULL, NULL, '2020-01-16', '2023-11-02', '2024-04-04', '2025-07-08', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, '2025-07-26 04:15:51', '2025-08-24 16:55:01', '2025-09-05 23:47:51', NULL, false, false, false, NULL, NULL, NULL, 0, 0);


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.users VALUES (1, 'Dean CAS', 'dean.cas@university.edu', '2025-09-04 22:28:48', '$2y$12$9OBX6RLUAesA9g0zVbtdc.1HTC2UI94DyqfswD0REWl1ffQSY.B26', NULL, '2025-09-04 22:28:48', '2025-09-04 22:28:48', 'dean_cas', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, NULL, NULL, 'dean', '[]', false, true);
INSERT INTO public.users VALUES (2, 'Associate Dean CAS', 'assoc.dean.cas@university.edu', '2025-09-04 22:28:49', '$2y$12$6CHmC1Vn4Nf9XNLbhflKYOnpir2I2UW.qxwIc3QD/powo8wdlGWXe', NULL, '2025-09-04 22:28:49', '2025-09-04 22:28:49', 'assoc_dean_cas', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, NULL, NULL, 'associate_dean', '[]', false, true);
INSERT INTO public.users VALUES (3, 'Dean COE', 'dean.coe@university.edu', '2025-09-04 22:28:49', '$2y$12$nIAIB9W9msunVGRFclTrve7kboIZTUwupeavT7ngpz0CAVbQTLgEG', NULL, '2025-09-04 22:28:49', '2025-09-04 22:28:49', 'dean_coe', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, NULL, NULL, 'dean', '[]', false, true);
INSERT INTO public.users VALUES (4, 'Associate Dean COE', 'assoc.dean.coe@university.edu', '2025-09-04 22:28:49', '$2y$12$Zq5P0MMxPjSLWfCz8Hmc0.LSwkOknzEkF0ghZ61.CSigc44OwDkKu', NULL, '2025-09-04 22:28:49', '2025-09-04 22:28:49', 'assoc_dean_coe', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, NULL, NULL, 'associate_dean', '[]', false, true);
INSERT INTO public.users VALUES (5, 'Dean COB', 'dean.cob@university.edu', '2025-09-04 22:28:49', '$2y$12$Rs55OD17WSLtSWTcx/pcJuQapqMOZcq.HApxT5piYTN0l9q1/wQca', NULL, '2025-09-04 22:28:49', '2025-09-04 22:28:49', 'dean_cob', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 8, NULL, NULL, NULL, 'dean', '[]', false, true);
INSERT INTO public.users VALUES (6, 'Associate Dean COB', 'assoc.dean.cob@university.edu', '2025-09-04 22:28:50', '$2y$12$A0O2HuMhDFZ1nt65HcOChe9vemelepDHvn.tSqlmaZS5PHwaz4le.', NULL, '2025-09-04 22:28:50', '2025-09-04 22:28:50', 'assoc_dean_cob', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 8, NULL, NULL, NULL, 'associate_dean', '[]', false, true);
INSERT INTO public.users VALUES (7, 'Dean COM', 'dean.com@university.edu', '2025-09-04 22:28:50', '$2y$12$jcSA9kd3EVN5OU8uExPih.knI6imrbdHMe6y57d19utMCKNa3HwlG', NULL, '2025-09-04 22:28:50', '2025-09-04 22:28:50', 'dean_com', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 9, NULL, NULL, NULL, 'dean', '[]', false, true);
INSERT INTO public.users VALUES (8, 'Associate Dean COM', 'assoc.dean.com@university.edu', '2025-09-04 22:28:50', '$2y$12$J5eAsRZISSNa14bJcd0hJeBZtQ16f2QrWDCC1jSsba3CcRNBFidr2', NULL, '2025-09-04 22:28:50', '2025-09-04 22:28:50', 'assoc_dean_com', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 9, NULL, NULL, NULL, 'associate_dean', '[]', false, true);
INSERT INTO public.users VALUES (9, 'Director SCS', 'director.scs@university.edu', '2025-09-04 22:28:50', '$2y$12$eNuW/aDly2hKBTxeRfKe6.xPCTsypiBy9BlnM73iCiS8aLE7tNZEe', NULL, '2025-09-04 22:28:50', '2025-09-04 22:28:50', 'director_scs', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, 3, NULL, NULL, 'director', '[]', false, true);
INSERT INTO public.users VALUES (10, 'Director SNS', 'director.sns@university.edu', '2025-09-04 22:28:51', '$2y$12$m3lPC9UWTegl9IksR5bVJe.u5uFAYPvH//zZ0KLkO33EylzyGpcL.', NULL, '2025-09-04 22:28:51', '2025-09-04 22:28:51', 'director_sns', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 9, 4, NULL, NULL, 'director', '[]', false, true);
INSERT INTO public.users VALUES (12, 'Head PHYS', 'head.phys@university.edu', '2025-09-04 22:28:51', '$2y$12$n.oopnOKwaHlrT0rEYxlTe99pXAfdcqQmzzQBhheVnY3NQvWX6BYy', NULL, '2025-09-04 22:28:51', '2025-09-04 22:28:51', 'head_phys', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 5, NULL, 'department_head', '[]', false, true);
INSERT INTO public.users VALUES (13, 'Head ENGL', 'head.engl@university.edu', '2025-09-04 22:28:51', '$2y$12$6StFcZJ699fonV080v3PFO3nU8UyKq3XzBLQ1ql1VsX1gdfxxntra', NULL, '2025-09-04 22:28:51', '2025-09-04 22:28:51', 'head_engl', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 6, NULL, 'department_head', '[]', false, true);
INSERT INTO public.users VALUES (14, 'Head ECE', 'head.ece@university.edu', '2025-09-04 22:28:51', '$2y$12$GnZJzobS8woguWXWXHLMk.qcj4oU4autsDxZsW1Hl4Q3Z0udpPbui', NULL, '2025-09-04 22:28:51', '2025-09-04 22:28:51', 'head_ece', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, 7, NULL, 'department_head', '[]', false, true);
INSERT INTO public.users VALUES (15, 'Head MECH', 'head.mech@university.edu', '2025-09-04 22:28:52', '$2y$12$f.42UaNmNVtLYwCUscYKGusjcdXsWMhsM6aJxb78Kma5y8I147rqy', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52', 'head_mech', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, 8, NULL, 'department_head', '[]', false, true);
INSERT INTO public.users VALUES (16, 'Head CS', 'head.cs@university.edu', '2025-09-04 22:28:52', '$2y$12$orguIz5IPqvYTRktUvA3ZeHRWcr.6i.o9YxPVOMlvt08YVAlUKdWK', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52', 'head_cs', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, 3, 9, NULL, 'department_head', '[]', false, true);
INSERT INTO public.users VALUES (17, 'Head ACCT', 'head.acct@university.edu', '2025-09-04 22:28:52', '$2y$12$8/vSyHh7dwLcgcuyRWB0fOHX/3V6y4D263tKgeZvBGDYYxGcx.gc.', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52', 'head_acct', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 8, NULL, 10, NULL, 'department_head', '[]', false, true);
INSERT INTO public.users VALUES (18, 'Head MGMT', 'head.mgmt@university.edu', '2025-09-04 22:28:52', '$2y$12$xIIEUZ8PGmw07mgAHvu69.7ydLbSQFtw3T3P3rLENDCpNWIDFHrvC', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52', 'head_mgmt', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 8, NULL, 11, NULL, 'department_head', '[]', false, true);
INSERT INTO public.users VALUES (19, 'Prof. MATH 1', 'math.faculty1@university.edu', '2025-09-04 22:28:52', '$2y$12$64sVnk0.8jduH1pbOxF.beJi36MlxijFV/dlvJKyvDcjLtLknUaXC', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52', 'math_faculty1', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 4, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (20, 'Prof. MATH 2', 'math.faculty2@university.edu', '2025-09-04 22:28:53', '$2y$12$A/FC4rs8QVWv72DBRs8g2.avb7LCqIxRVGrlTUSuzjU19g23a2vCq', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53', 'math_faculty2', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 4, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (21, 'Prof. MATH 3', 'math.faculty3@university.edu', '2025-09-04 22:28:53', '$2y$12$ShkhMzZCBpp5Ae6J32pr/eiaIr2C0Na2W8.cAGjiqH7E9d9bNlLJ6', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53', 'math_faculty3', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 4, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (22, 'Prof. PHYS 1', 'phys.faculty1@university.edu', '2025-09-04 22:28:53', '$2y$12$uG21T0CpTTTWYrreDgwWOeza.095nQPKKXQpUFMNB0dTJcufAiuKG', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53', 'phys_faculty1', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 5, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (23, 'Prof. PHYS 2', 'phys.faculty2@university.edu', '2025-09-04 22:28:53', '$2y$12$RdtL5YXYoMhEZogIVUfROeOwzCLbJpJ/XvmcAZetFSWt6CVJ5Agj6', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53', 'phys_faculty2', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 5, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (24, 'Prof. PHYS 3', 'phys.faculty3@university.edu', '2025-09-04 22:28:54', '$2y$12$H2G2eHl8V28HusEKw.x7jepBpHLqDtXFnAEU7RgHpGFElwYUTFLMa', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54', 'phys_faculty3', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 5, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (25, 'Prof. PHYS 4', 'phys.faculty4@university.edu', '2025-09-04 22:28:54', '$2y$12$qLrCvKO5EGE8mngDQTFmd.rj9VJcEfabMxcryONx1DgVkz2R0LXyu', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54', 'phys_faculty4', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 5, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (26, 'Prof. ENGL 1', 'engl.faculty1@university.edu', '2025-09-04 22:28:54', '$2y$12$u3r5kOAwcCaFrxB8NQnXLuLb2KjEL/mdTzSoTXOJrmYq0QYIHzeZq', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54', 'engl_faculty1', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 6, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (27, 'Prof. ENGL 2', 'engl.faculty2@university.edu', '2025-09-04 22:28:54', '$2y$12$yB9XLsLke4MaZIFfdtXklOdpulGXFOkzDzsbQEWTE0mfcdJCLY7h.', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54', 'engl_faculty2', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 6, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (28, 'Prof. ENGL 3', 'engl.faculty3@university.edu', '2025-09-04 22:28:55', '$2y$12$3bgXUJ9rt/b/40N7DKPiUOwetfp4Uh6IfLdTa7eziz4Bua448nLYS', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55', 'engl_faculty3', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 6, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (29, 'Prof. ENGL 4', 'engl.faculty4@university.edu', '2025-09-04 22:28:55', '$2y$12$09WnoHo7cJc6CHK2Isfgg.UGZUw7bEJtm0ZSBigh1iXagrsh10iOC', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55', 'engl_faculty4', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 6, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (30, 'Prof. ENGL 5', 'engl.faculty5@university.edu', '2025-09-04 22:28:55', '$2y$12$B.KKVFkI28PX1wz.yCkrpeeonTKhTkWawSP2qGi3LYw6n0xFKRNCS', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55', 'engl_faculty5', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 6, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (31, 'Prof. ECE 1', 'ece.faculty1@university.edu', '2025-09-04 22:28:55', '$2y$12$qOQWIoArv3GRaVrqIzJWAOxdGXUDvgBERl45ptPxHY6thF8LZr3nm', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55', 'ece_faculty1', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, 7, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (32, 'Prof. ECE 2', 'ece.faculty2@university.edu', '2025-09-04 22:28:55', '$2y$12$.0dOCYRHHRbNCyN5v8IEreRk.JQJwOttHBH4OxfU/Ixbh4zu01vPq', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55', 'ece_faculty2', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, 7, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (33, 'Prof. ECE 3', 'ece.faculty3@university.edu', '2025-09-04 22:28:56', '$2y$12$WuR4ODhcQGgRa9K3Z5C.8ehF9jTc7vegXYFFDRmIhe4FU0v2hDVB.', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56', 'ece_faculty3', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, 7, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (34, 'Prof. MECH 1', 'mech.faculty1@university.edu', '2025-09-04 22:28:56', '$2y$12$3zqEI.OWYXVzENrTKgf9xey3C0bWSYymI/0oxep0Rk/GF5Sewk6Kq', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56', 'mech_faculty1', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, 8, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (35, 'Prof. MECH 2', 'mech.faculty2@university.edu', '2025-09-04 22:28:56', '$2y$12$Jj/ff.3m2AQLu9HfIJNVKeQAnIcP4DM7n8AQQfVeOcB.qlmZP.SX2', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56', 'mech_faculty2', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, 8, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (36, 'Prof. MECH 3', 'mech.faculty3@university.edu', '2025-09-04 22:28:56', '$2y$12$bh0GRCaTu6uoCJHvZsdXFu/rFdgBhotyeTFD2zWyPbCSBmwHTphre', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56', 'mech_faculty3', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, 8, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (37, 'Prof. MECH 4', 'mech.faculty4@university.edu', '2025-09-04 22:28:56', '$2y$12$rTo3CCYvowB2buyf04NONO5CDtdo7L5YKSroj3q3crrFht5clnrQa', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56', 'mech_faculty4', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, 8, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (38, 'Prof. MECH 5', 'mech.faculty5@university.edu', '2025-09-04 22:28:57', '$2y$12$NUKzRT.UEVmagWCWb1H/NOZHu66V.WVvl2smfyMMIxMQJvJO0GrUS', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57', 'mech_faculty5', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 7, NULL, 8, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (40, 'Prof. CS 2', 'cs.faculty2@university.edu', '2025-09-04 22:28:57', '$2y$12$D7wPJVbckwwUZJlXoPyo7exO5tg06UQI.ij/jQFdlR/E7siSFMzwa', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57', 'cs_faculty2', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, 3, 9, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (41, 'Prof. CS 3', 'cs.faculty3@university.edu', '2025-09-04 22:28:57', '$2y$12$15oYryAqx.NkLMZInpW9k./EOsFlXBsvVy2abXJKGSGbotK0r/mq.', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57', 'cs_faculty3', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, 3, 9, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (42, 'Prof. ACCT 1', 'acct.faculty1@university.edu', '2025-09-04 22:28:58', '$2y$12$ftzYP2TCd7Ko.egEWJ3FU.JOR4AP3IPPr.hIbXbJ58WIC1QGxPgrK', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58', 'acct_faculty1', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 8, NULL, 10, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (43, 'Prof. ACCT 2', 'acct.faculty2@university.edu', '2025-09-04 22:28:58', '$2y$12$XE7759.dan8zJHGd.PZ3Ve4KtXasAbol1SVyWttzgglOumDhptXya', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58', 'acct_faculty2', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 8, NULL, 10, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (44, 'Prof. ACCT 3', 'acct.faculty3@university.edu', '2025-09-04 22:28:58', '$2y$12$l2K89T2MKrRXUgcnWeG6o.lUo/LYuDgMq7agXdLBa488Pjx7VKoQC', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58', 'acct_faculty3', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 8, NULL, 10, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (45, 'Prof. MGMT 1', 'mgmt.faculty1@university.edu', '2025-09-04 22:28:58', '$2y$12$.4RyTC8R/aI2JKZTQ1WK4uG0d8lcneuTsFrWPA4nbZ4X4J8NXySQe', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58', 'mgmt_faculty1', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 8, NULL, 11, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (46, 'Prof. MGMT 2', 'mgmt.faculty2@university.edu', '2025-09-04 22:28:58', '$2y$12$CPXwyOwqq4/ENuAGdSwkve/eymzqQpZxGGiLG/W1JfptXJp8kwf.G', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58', 'mgmt_faculty2', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 8, NULL, 11, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (47, 'Prof. MGMT 3', 'mgmt.faculty3@university.edu', '2025-09-04 22:28:59', '$2y$12$q3.rnazVVLTvC8SYHKr1NehtmqBbMT2OjutdZvO2kBMR6K6.TcCQe', NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59', 'mgmt_faculty3', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 8, NULL, 11, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (11, 'Head MATH', 'head.math@university.edu', '2025-09-04 22:28:51', '$2y$12$s2eL8Qc6Qt2ljiKHSlaCUOwOtYSrDfVHssBO8AnYVxljUZjbhIg0O', NULL, '2025-09-04 22:28:51', '2025-09-04 22:28:59', 'head_math', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, 6, NULL, 4, NULL, 'department_head', '[9]', false, true);
INSERT INTO public.users VALUES (49, 'Academic Administrator', 'academic@intellicampus.edu', '2025-09-05 15:45:16', '$2y$12$1mvwUENyrmtFeE2MfmeiLObSsmFpWKQHoOs8wf0VtuirH80.8CwSK', NULL, '2025-09-05 15:45:16', '2025-09-05 15:45:16', '.2', 'admin', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (50, 'Financial Administrator', 'finance@intellicampus.edu', '2025-09-05 15:45:30', '$2y$12$UWToz9F25AO7wHYtXiTZxuZ3BB7obkFlCLZcBAjZsknL49J8szI7.', NULL, '2025-09-05 15:45:30', '2025-09-05 15:45:30', '.3', 'admin', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (52, 'Student Advisor', 'advisor@intellicampus.edu', '2025-09-05 15:46:14', '$2y$12$DbgK3z2kQxm9Wp.aaqJPCum/Dhpn7eu.HcwWjf0GvRkahWxUIuhmu', NULL, '2025-09-05 15:46:14', '2025-09-05 15:46:14', '.5', 'staff', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (53, 'Administrative Staff', 'staff@intellicampus.edu', '2025-09-05 15:46:41', '$2y$12$707dcfo6PJwqdkJSaieCTeGQzkEkjC8qNvDv2MXsnqk3VoWcw1.Ze', NULL, '2025-09-05 15:46:41', '2025-09-05 15:46:41', '.6', 'staff', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (54, 'System Auditor', 'auditor@intellicampus.edu', '2025-09-05 15:47:07', '$2y$12$PXPYIaKncJfhfwbRnzAcTemiEx6KewFODpK0PnrpgpBK2dv9GgYuW', NULL, '2025-09-05 15:47:07', '2025-09-05 15:47:07', '.7', 'staff', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (175, 'Cassandre Hayes', 'dejah.stroman@example.com', NULL, '$2y$12$lstdWMpVgutqDJaK07cQZepu6rK7FO9.s68uGKMOv3gc4GcrAtLcy', NULL, '2025-09-17 15:38:42', '2025-09-17 15:38:42', 'dejah.stroman', 'applicant', 'active', NULL, 'Cassandre', 'Hannah', 'Hayes', 'other', '2006-10-30', 'Chad', NULL, NULL, '302.353.1587', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:42+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (176, 'Jaiden Watsica', 'barrows.rowena@example.org', NULL, '$2y$12$wjlHjwx8EL292JHSzjfAHeF2EDOlyrBkv8JC317OJdq0oGdUe12tS', NULL, '2025-09-17 15:38:43', '2025-09-17 15:38:43', 'barrows.rowena', 'applicant', 'active', NULL, 'Jaiden', 'Randy', 'Watsica', 'female', '2000-09-29', 'Zimbabwe', NULL, NULL, '310-988-1977', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:43+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (48, 'Super Administrator', 'admin@intellicampus.edu', '2025-09-05 14:54:55', '$2y$12$ly8GfvRWUheLMIZxqugT8O9bjKjcQI7peBiM5zi4x.Yza8Lh1AtGO', NULL, '2025-09-05 14:54:56', '2025-09-19 16:22:47', '.', 'admin', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, '2025-09-19 16:22:47', NULL, false, 0, NULL, NULL, false, NULL, NULL, 48, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (39, 'Prof. CS 1', 'cs.faculty1@university.edu', '2025-09-04 22:28:57', '$2y$12$4ScCRn1tcwPay9vvxlfdcujvD1x6EwyKb1SW98ubZtT3ZcSlL0WUu', 'MbJvhkn3ybD9GepMFhiyATvtXxGYuvHTo1aaldymfsGBRAbqgcNRtzdaS8oT', '2025-09-04 22:28:57', '2025-09-20 20:13:10', 'cs_faculty1', 'faculty', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, 3, 9, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (51, 'University Registrar', 'registrar@intellicampus.edu', '2025-09-05 15:45:45', '$2y$12$zM9jE8XnbEdifNSvY7.I9epV6ukqGD1WpjWQT.W0IzJmMHDG7LlfW', 'GOR2upc0SVwXOzGQfOVtq3d1EZbY6234g4hJVmiYF3NC2wCHasWBj2XvGS6D', '2025-09-05 15:45:45', '2025-09-21 11:54:24', '.4', 'staff', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (177, 'Autumn Heathcote', 'boehm.cecilia@example.com', NULL, '$2y$12$HHqeJ3G8b5XdKjAFqi8x2OWz.xmLqSSQwHMiVkz7Ou56APvk5XQxG', NULL, '2025-09-17 15:38:43', '2025-09-17 15:38:43', 'boehm.cecilia', 'applicant', 'active', NULL, 'Autumn', NULL, 'Heathcote', 'female', '1997-04-22', 'Tokelau', NULL, NULL, '559-834-9230', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:43+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (178, 'Kory Mueller', 'bosco.mohamed@example.net', NULL, '$2y$12$ndY7oQLxQUY4vixir8nZZuPAMiaBByHnpmNiORBfCWAOc..Q9TLQO', NULL, '2025-09-17 15:38:43', '2025-09-17 15:38:43', 'bosco.mohamed', 'applicant', 'active', NULL, 'Kory', NULL, 'Mueller', 'male', '2004-12-29', 'Paraguay', NULL, NULL, '(737) 219-4294', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:43+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (179, 'Arianna Schuppe', 'alden.konopelski@example.com', NULL, '$2y$12$izcQjMbAZgZp6yOUSyJPiePdWyNaT5sLAgZV6yar1AQv9o8dpcUzS', NULL, '2025-09-17 15:38:44', '2025-09-17 15:38:44', 'alden.konopelski', 'applicant', 'active', NULL, 'Arianna', 'Allison', 'Schuppe', 'other', '2002-09-13', 'South Africa', NULL, NULL, '(351) 515-8521', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:44+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (180, 'Jacques Reilly', 'bruen.amaya@example.org', NULL, '$2y$12$iXFs16T9VGt9Yp6rWiJSJuxj2VHWPidXWM8lQ4SRSlrisbxnWiWuG', NULL, '2025-09-17 15:38:44', '2025-09-17 15:38:44', 'bruen.amaya', 'applicant', 'active', NULL, 'Jacques', 'Oma', 'Reilly', 'other', '1997-06-17', 'Mozambique', NULL, NULL, '(385) 529-6747', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:44+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (181, 'Tiara McLaughlin', 'mlangworth@example.net', NULL, '$2y$12$GJuF2BE/.NUl1OwJnYhYb.bpUbqrINAb1CA5MY/sMu1MTNhc3pk3S', NULL, '2025-09-17 15:38:44', '2025-09-17 15:38:44', 'mlangworth', 'applicant', 'active', NULL, 'Tiara', NULL, 'McLaughlin', 'female', '1998-09-23', 'Kazakhstan', NULL, NULL, '+19103904067', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:44+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (182, 'Maximo Hauck', 'bruen.orland@example.net', NULL, '$2y$12$7wEGDR3R0PC7Wgu12LswiOseazEV9t/grBrP62ED.fSnCaVpLkS.i', NULL, '2025-09-17 15:38:45', '2025-09-17 15:38:45', 'bruen.orland', 'applicant', 'active', NULL, 'Maximo', 'Tommie', 'Hauck', 'other', '2008-02-09', 'Malta', NULL, NULL, '937.721.6334', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:45+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (183, 'Vivienne Rippin', 'ola.harris@example.net', NULL, '$2y$12$F6l6sL.WqXYrSkTrdv/eiuY.abLKpyeiFakr85bsu6CjqGGm6EEPC', NULL, '2025-09-17 15:38:45', '2025-09-17 15:38:45', 'ola.harris', 'applicant', 'active', NULL, 'Vivienne', NULL, 'Rippin', 'male', '2008-03-30', 'Ireland', NULL, NULL, '+1.435.394.3515', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:45+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (184, 'Axel Spencer', 'nora67@example.org', NULL, '$2y$12$l0EkRahqc.t3abZ.MVaeUeWF8seok.8B1U/TGg9j9W1cnzYEitc9C', NULL, '2025-09-17 15:38:45', '2025-09-17 15:38:45', 'nora67', 'applicant', 'active', NULL, 'Axel', 'Irwin', 'Spencer', 'male', '2007-03-10', 'Central African Republic', NULL, NULL, '586-659-3188', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:45+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (185, 'Helen Abshire', 'thea76@example.net', NULL, '$2y$12$DtmWIbKPZRlxvYV/VB.U0uJGNETCshVhwekuD/rqrGpgP1DoAnWAS', NULL, '2025-09-17 15:38:45', '2025-09-17 15:38:45', 'thea76', 'applicant', 'active', NULL, 'Helen', 'Reed', 'Abshire', 'male', '2007-05-04', 'Kyrgyz Republic', NULL, NULL, '+1 (657) 893-9836', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:45+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (186, 'Hadley Zulauf', 'jovan.little@example.net', NULL, '$2y$12$nrmQM96UMrjkuJRoStNFI.fNbObsEUIMEc5N4Ljl/.SHSpOGMVgOm', NULL, '2025-09-17 15:38:46', '2025-09-17 15:38:46', 'jovan.little', 'applicant', 'active', NULL, 'Hadley', 'Daphnee', 'Zulauf', 'other', '2007-11-14', 'Canada', NULL, NULL, '1-541-243-7613', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:46+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (187, 'Lura Bashirian', 'feil.sigrid@example.org', NULL, '$2y$12$XhqJD5r6.TVXvAjcSs0d3.E306Jnl0oJL6tN/FHXReHhU2muTMgti', NULL, '2025-09-17 15:38:46', '2025-09-17 15:38:46', 'feil.sigrid', 'applicant', 'active', NULL, 'Lura', NULL, 'Bashirian', 'female', '2002-04-25', 'Swaziland', NULL, NULL, '740-787-3333', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:46+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (188, 'Colt Bashirian', 'mgutmann@example.org', NULL, '$2y$12$nNZKiV9TEOU0g8V.er6GXuhc92Wjav21FF8m8e6Qhy6s7o1pFBZbm', NULL, '2025-09-17 15:38:46', '2025-09-17 15:38:46', 'mgutmann', 'applicant', 'active', NULL, 'Colt', NULL, 'Bashirian', 'other', '1996-02-02', 'Mali', NULL, NULL, '564.850.0696', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:46+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (189, 'Chet Considine', 'lockman.danika@example.net', NULL, '$2y$12$uFQaGXzsW6aNr9ITddFIfORkNw0NYgFTiVg7m/9O3kWIz0ev0yPba', NULL, '2025-09-17 15:38:46', '2025-09-17 15:38:46', 'lockman.danika', 'applicant', 'active', NULL, 'Chet', 'Gregory', 'Considine', 'male', '1996-11-04', 'Malaysia', NULL, NULL, '615-464-3528', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:46+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (190, 'Rosario Lowe', 'dlueilwitz@example.net', NULL, '$2y$12$./DxV1MHnzvQNZzJV7a28.8F/y6rZYbww4TxmdzdvIYvWn4Wt.WhO', NULL, '2025-09-17 15:38:46', '2025-09-17 15:38:46', 'dlueilwitz', 'applicant', 'active', NULL, 'Rosario', NULL, 'Lowe', 'other', '2002-12-13', 'Svalbard & Jan Mayen Islands', NULL, NULL, '+1-479-443-6412', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:46+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (191, 'Arden Kessler', 'shane.zieme@example.net', NULL, '$2y$12$3FTez8sHn/QDOrfnLVE8Be4Ce1pi4yYPgkIeIsbctz797j4xW8aYW', NULL, '2025-09-17 15:38:47', '2025-09-17 15:38:47', 'shane.zieme', 'applicant', 'active', NULL, 'Arden', 'Kobe', 'Kessler', 'female', '2007-10-01', 'Cote d''Ivoire', NULL, NULL, '+1-606-671-1750', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:47+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (192, 'Alda Hand', 'ewell.vonrueden@example.org', NULL, '$2y$12$ssSG6q3rVIPGUeX/u1VyNeKJ6fvEvRIEmWeEtD.Jr.4I6Guwex.sK', NULL, '2025-09-17 15:38:47', '2025-09-17 15:38:47', 'ewell.vonrueden', 'applicant', 'active', NULL, 'Alda', 'Sarai', 'Hand', 'female', '2003-01-17', 'Wallis and Futuna', NULL, NULL, '1-559-415-8627', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:47+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (193, 'Luciano Schneider', 'shannon19@example.net', NULL, '$2y$12$3NBq6FxiVw6bTiii/Lb6WeNH4RAlYIsbKp5QMPXyCsHbMFrnsh/2W', NULL, '2025-09-17 15:38:47', '2025-09-17 15:38:47', 'shannon19', 'applicant', 'active', NULL, 'Luciano', 'Edyth', 'Schneider', 'male', '2006-01-24', 'Saint Lucia', NULL, NULL, '615-696-9572', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:47+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (194, 'Clay Schmidt', 'william.blick@example.org', NULL, '$2y$12$9s5gxAgKHPXJhfEmwljeSODKLtBKieUpsiUie8JoroV5HgMcbKEr.', NULL, '2025-09-17 15:38:47', '2025-09-17 15:38:47', 'william.blick', 'applicant', 'active', NULL, 'Clay', 'Adam', 'Schmidt', 'other', '2004-01-23', 'Venezuela', NULL, NULL, '1-534-559-2079', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:47+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (195, 'Celia Kshlerin', 'maxime54@example.net', NULL, '$2y$12$pIzA4Cqaj/noM8lZ5Ypplun4Jp/xScjLR50lIvShx2JboH5.f0.gi', NULL, '2025-09-17 15:38:48', '2025-09-17 15:38:48', 'maxime54', 'applicant', 'active', NULL, 'Celia', 'Naomi', 'Kshlerin', 'female', '2001-06-14', 'Lithuania', NULL, NULL, '754-345-6076', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:48+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (196, 'Cali Kovacek', 'rosemary91@example.com', NULL, '$2y$12$tj3A3jW18XVJp18x3YfAQ.4AX4v3wHWqZvOnhQP5uwcnKPTgi3vk6', NULL, '2025-09-17 15:38:48', '2025-09-17 15:38:48', 'rosemary91', 'applicant', 'active', NULL, 'Cali', 'Maeve', 'Kovacek', 'male', '2000-10-01', 'Hong Kong', NULL, NULL, '352.579.7995', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{"type_history": [{"to": "applicant", "from": null, "reason": "Account created as applicant", "changed_at": "2025-09-17T15:38:48+00:00", "changed_by": "system", "ip_address": "127.0.0.1"}]}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (155, 'Test  Student', 'test.student@example.com', '2025-09-06 00:39:34', '$2y$12$LswGnPqvaGJQu1Rl.3xd5O4BmhKS6wIaFqaINFbeM8yx7ky5gZLey', NULL, '2025-09-06 00:39:34', '2025-09-19 15:43:53', 'tstudent', 'student', 'active', NULL, 'Test', NULL, 'Student', 'male', '2000-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, '2025-09-19 15:43:53', '2025-09-07 05:07:50', false, 0, NULL, NULL, false, NULL, NULL, 155, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (160, 'John Smith', 'john.smith@intellicampus.edu', '2025-09-12 15:49:15', '$2y$12$2Kg6nhgj.DQVSW6Y87lCveTvktFLm888NMMbp9VbL7hXThvKl4vu.', NULL, '2025-09-12 15:49:15', '2025-09-12 15:49:15', '.8', 'applicant', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (168, 'Jane Test', 'jane.applicant@test.com', '2025-09-16 11:49:11', '$2y$12$tMSQqs.eAnKyflsaJ1Egz.JtaDmuPvpnVK8cFsAOyuZhwi5wFUrti', NULL, '2025-09-16 10:29:18', '2025-09-16 11:49:13', '.14', 'applicant', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (161, 'Sarah Johnson', 'sarah.johnson@intellicampus.edu', '2025-09-12 15:49:16', '$2y$12$PdLUmebE0SrvwKrsW/eKPO6lrekQ8JLikAQpk0dW5rVTemHwSy5La', NULL, '2025-09-12 15:49:16', '2025-09-12 15:49:16', '.9', 'applicant', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (162, 'Dr. Michael Brown', 'michael.brown@intellicampus.edu', '2025-09-12 15:49:16', '$2y$12$QBr6atKddplh4z3ZHFSMf.l7YEWTxJygUrsho95uzjhmmjheTJ47i', NULL, '2025-09-12 15:49:16', '2025-09-12 15:49:16', '.10', 'applicant', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (163, 'Diggy Demo', 'diggygibson.rg@gmail.com', NULL, '$2y$12$LRCg162Hs8abv3zv3.Zmhu81Z2Qrm7XmGlkaSVtd25C04JqZ/IbCG', NULL, '2025-09-14 20:53:26', '2025-09-14 20:53:26', '.11', 'applicant', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (150, 'Jaylin  Lynch', 'bryana.pollich@example.com', '2025-09-05 23:47:50', '$2y$12$LwlGibSDrpDmhX96gvb83uyfJC7jJjhFkV7mEpS2Db9IpS6901WOS', NULL, '2025-09-05 23:47:50', '2025-09-05 23:47:50', 'jlynch', 'student', 'active', NULL, 'Jaylin', NULL, 'Lynch', 'male', '1999-09-05', 'Greenland', '5804373630', NULL, '949.868.1136', NULL, '4062 Abdullah Plaza, Idellview, AL 02092-8132', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Anita Durgan III', NULL, '+1 (574) 733-8950', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (151, 'Eldora Matilda King', 'xdicki@example.net', '2025-09-05 23:47:51', '$2y$12$HwCtr6ulWN2hXCwAqVuT3.VdPyoAcU72e62.oIOgJLASjCwUUWk6K', NULL, '2025-09-05 23:47:51', '2025-09-05 23:47:51', 'eking', 'student', 'suspended', NULL, 'Eldora', 'Matilda', 'King', 'male', '2000-10-16', 'Greenland', '1978957902', NULL, '+1-308-515-8690', NULL, '372 Aditya Union, New Heather, GA 01917', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ronny Padberg', NULL, '+1-848-272-6844', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (171, 'Diggy Gibson', 'rgibson@diggysolutions.com', '2025-09-18 11:34:34', '$2y$12$LRpfZ8R6DD0CRa2elv1DuuvDqwOQynCWpA9nlRtQ8Hps5vXP8IZBG', NULL, '2025-09-16 11:26:35', '2025-09-18 11:34:34', '.17', 'applicant', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, 171, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (167, 'John Applicant', 'applicant@example.com', '2025-09-16 11:49:11', '$2y$12$hRAyX8rOg.Og.ScPqpSnSuUcvTLMYGWkraN1I5kkDLZxpT7oPZMRi', NULL, '2025-09-16 10:29:18', '2025-09-16 11:49:13', '.13', 'applicant', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (169, 'David International', 'intl.applicant@test.com', '2025-09-16 11:49:12', '$2y$12$uy7A0KARiCtOlla4p/Gjr.ucEvPn6EOkiQq3wrBLBzW.I4vO5BEfW', NULL, '2025-09-16 10:29:18', '2025-09-16 11:49:13', '.15', 'applicant', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (170, 'Sarah Graduate', 'grad.applicant@test.com', '2025-09-16 11:49:12', '$2y$12$iCLvwxIIWMSi0BekXJf/1.hqgeXCa8nxJeya6Dv4zKClaWyO.R83G', NULL, '2025-09-16 10:29:18', '2025-09-16 11:49:13', '.16', 'applicant', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (164, 'Test Applicant', 'test.applicant@test.com', NULL, '$2y$12$KuSO1HBVEAFBejwj3/N4aOYvgzYBIFFtXV8Kd0.dBXY35kuuR1MWG', NULL, '2025-09-16 04:35:04', '2025-09-16 04:35:04', '.12', 'applicant', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, false, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (56, 'Robert James Johnson', 'robert.johnson@university.edu', '2025-09-05 23:47:26', '$2y$12$Jmj88KwwyNAy64TCvMaEy.SLgNX6ZWRlyWKF8fvpqCdhmYTWpqpm.', NULL, '2025-09-05 23:47:26', '2025-09-05 23:47:26', 'rjohnson', 'student', 'active', NULL, 'Robert', 'James', 'Johnson', 'male', '2001-11-30', 'American', '555666777', NULL, '+1234567897', NULL, '555 University Blvd, New York, NY 10003', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Sarah Johnson', NULL, '+1234567899', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (58, 'David Lee Kim', 'david.kim@university.edu', '2025-09-05 23:47:26', '$2y$12$AyYVc3q.L4zxkE6MW/OVZerH8rz6bmkBzoZ2CNtWFPvdHUzuh3WDW', NULL, '2025-09-05 23:47:26', '2025-09-05 23:47:26', 'dkim', 'student', 'inactive', NULL, 'David', 'Lee', 'Kim', 'male', '1999-03-15', 'South Korean', '999888777', NULL, '+1234567901', NULL, '888 Student Hall, New York, NY 10005', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (62, 'Benedict  Emard', 'virginie.lesch@example.net', '2025-09-05 23:47:27', '$2y$12$EI3ndf8IKA668uLr/VekAeM4hJESHkPpnrACBC0SdOqlh8Aq7sXji', NULL, '2025-09-05 23:47:27', '2025-09-05 23:47:27', 'bemard', 'student', 'inactive', NULL, 'Benedict', NULL, 'Emard', 'male', '2002-11-07', 'Costa Rica', '0002488987', NULL, '480-537-6815', NULL, '31563 Claudia Summit, Annaborough, AZ 80679', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Leola Crooks', NULL, '+1.618.384.7376', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (63, 'Magali  Barton', 'colt.lindgren@example.net', '2025-09-05 23:47:27', '$2y$12$CWEzZ3vQpOWx9lnsOD4Bo.uoLoPPsWQUU/PCj7gg30QBSxbURyKsO', NULL, '2025-09-05 23:47:27', '2025-09-05 23:47:27', 'mbarton', 'student', 'active', NULL, 'Magali', NULL, 'Barton', 'male', '1995-12-08', 'Gibraltar', '2418106956', NULL, '1-248-277-6508', NULL, '614 Borer Mills, North Gladys, NV 24060', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Oma Legros', NULL, '231-313-3439', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (66, 'Oliver  Nolan', 'ckulas@example.net', '2025-09-05 23:47:28', '$2y$12$b39LKoEXNzsl9Jomog8Pq.LWVfka1Y5VxU22cIVhpIiLOOu/X2gci', NULL, '2025-09-05 23:47:28', '2025-09-05 23:47:28', 'onolan', 'student', 'inactive', NULL, 'Oliver', NULL, 'Nolan', 'male', '2006-08-12', 'Ghana', '7761562493', NULL, '(727) 570-3186', NULL, '7285 Hackett Shore Suite 004, Josiestad, MI 09713', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rachel Mraz', NULL, '+14798447725', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (70, 'John Michael Doe', 'john.doe@university.edu', '2025-09-05 23:47:29', '$2y$12$KRAfPIFCfJTAa6MtBBddtu27vO7fPZcmDtkptz8ALQ3IxjcvW2/XC', NULL, '2025-09-05 23:47:29', '2025-09-05 23:47:29', 'jdoe', 'student', 'active', NULL, 'John', 'Michael', 'Doe', 'male', '2000-01-15', 'American', '123456789', NULL, '+1234567890', NULL, '123 Main St, New York, NY 10001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jane Doe', NULL, '+1234567893', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (72, 'Robert Easton Kautzer', 'gutkowski.mikel@example.org', '2025-09-05 23:47:30', '$2y$12$0JOR8F5SKDIstbuRHXguku0hCWKgOELerTYAGakd4VEvovZXadADy', NULL, '2025-09-05 23:47:30', '2025-09-05 23:47:30', 'rkautzer', 'student', 'suspended', NULL, 'Robert', 'Easton', 'Kautzer', 'male', '1996-12-27', 'French Southern Territories', '1416313742', NULL, '+1 (563) 982-4517', NULL, '661 Toni Gateway, Kennediport, MT 44786-3591', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ryley Kuhic', NULL, '754.872.9579', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (74, 'Vanessa Gina Heller', 'zaria.luettgen@example.org', '2025-09-05 23:47:30', '$2y$12$2qDcB8bYOrRBdMcOkkdyQOHtLB3Ex799siHALFa2TZXr.yeGPZFLe', NULL, '2025-09-05 23:47:30', '2025-09-05 23:47:30', 'vheller', 'student', 'inactive', NULL, 'Vanessa', 'Gina', 'Heller', 'male', '2001-06-17', 'Cameroon', '1425406920', NULL, '850.372.7998', NULL, '448 McClure Vista Apt. 302, Genovevachester, KY 42754', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Prof. Cydney Shields', NULL, '1-940-509-9281', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (76, 'Candace Kane D''Amore', 'junior73@example.com', '2025-09-05 23:47:31', '$2y$12$fADdvoWSMKSfh/n5pdIRXu.udJNaL6wXeQRZxrzjxERQiheoQ6B6q', NULL, '2025-09-05 23:47:31', '2025-09-05 23:47:31', 'cdamore', 'student', 'active', NULL, 'Candace', 'Kane', 'D''Amore', 'male', '1999-06-17', 'Antarctica (the territory South of 60 deg S)', '0903332099', NULL, '+1-929-417-2928', NULL, '494 Obie Plains Apt. 354, East Zachariah, MT 54885', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Donnie Balistreri', NULL, '(430) 421-3213', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (80, 'Maximus Rita Lueilwitz', 'athompson@example.net', '2025-09-05 23:47:32', '$2y$12$SGXQxxqK4tuDILAABUXtFuAMR6eEdh77gflMarvhDs8j52Zi0zp5G', NULL, '2025-09-05 23:47:32', '2025-09-05 23:47:32', 'mlueilwitz', 'student', 'active', NULL, 'Maximus', 'Rita', 'Lueilwitz', 'male', '2006-03-05', 'Nepal', '6694804976', NULL, '+1-539-884-3247', NULL, '8452 White Rapids, Port Nathanaelside, MA 66163-9425', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brown Lehner', NULL, '+1-458-747-6651', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (81, 'Jaleel  Jones', 'alexane45@example.com', '2025-09-05 23:47:32', '$2y$12$/vSdGWlWOpouyMbrlaGtOOmuxTPv.Sa2HbZXqeHDnA5GtyGstxWke', NULL, '2025-09-05 23:47:32', '2025-09-05 23:47:32', 'jjones', 'student', 'active', NULL, 'Jaleel', NULL, 'Jones', 'male', '2005-04-01', 'Mongolia', '1395758175', NULL, '+1-631-984-8907', NULL, '8332 Stark Plains Suite 844, North Malachichester, NH 84502-8094', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rosa Jakubowski', NULL, '+1-872-339-6423', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (89, 'Eve Nicolas Thiel', 'brooklyn97@example.net', '2025-09-05 23:47:34', '$2y$12$rdBnyg3RJm.Fr74kjLDaFubBW1qtuAURb3ZXLsas1.hqnbBAJyj02', NULL, '2025-09-05 23:47:34', '2025-09-05 23:47:34', 'ethiel', 'student', 'inactive', NULL, 'Eve', 'Nicolas', 'Thiel', 'male', '1999-09-30', 'Botswana', '2347832253', NULL, '+1.534.975.8206', NULL, '26812 Israel Parkways, Bellshire, CO 71782-6169', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dr. Jaylan Goyette V', NULL, '817-643-9470', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (90, 'Laura Pat Altenwerth', 'gina50@example.net', '2025-09-05 23:47:34', '$2y$12$pQtKf3ph8byCVYCBDM59XulbKL/t93Vy0rczHVLMX/xbFpampk/.q', NULL, '2025-09-05 23:47:34', '2025-09-05 23:47:34', 'laltenwerth', 'student', 'active', NULL, 'Laura', 'Pat', 'Altenwerth', 'male', '1996-11-16', 'China', '7560450004', NULL, '936.304.3019', NULL, '377 Joan Flats, Lake Callie, GA 73201-3937', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Gerry Abbott', NULL, '530.977.2302', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (92, 'Elvis Jeanie Gusikowski', 'keon58@example.net', '2025-09-05 23:47:35', '$2y$12$.iiGVX2EmicGznsUjAKsK.KKixm8cJgekqZoP7Luoa2zaHWbyr0Oa', NULL, '2025-09-05 23:47:35', '2025-09-05 23:47:35', 'egusikowski', 'student', 'active', NULL, 'Elvis', 'Jeanie', 'Gusikowski', 'male', '1997-04-15', 'Canada', '1348599011', NULL, '(878) 757-5060', NULL, '61655 Eloise Field Suite 809, East Lavinia, WI 73285', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jessica Frami', NULL, '401.657.7572', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (99, 'Greyson Edyth Veum', 'easter.klocko@example.net', '2025-09-05 23:47:37', '$2y$12$jQZdNlIBOzo27cUR4Qi1y.zBgyTRKCe2AInfZv0E3W0l2ijBDyIt6', NULL, '2025-09-05 23:47:37', '2025-09-05 23:47:37', 'gveum', 'student', 'active', NULL, 'Greyson', 'Edyth', 'Veum', 'male', '2006-10-10', 'Mauritania', '4385843680', NULL, '(856) 566-3996', NULL, '30156 Casper Corners Suite 208, Port Noeliahaven, NE 17690', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Berneice Leuschke', NULL, '+1-930-351-0924', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (100, 'Kurtis  McCullough', 'melany.graham@example.net', '2025-09-05 23:47:37', '$2y$12$5qrOKHKRNWlQOl2WEGFxFepWgc/rZvR9aQtpCnHVZx.P4v4AOUlnO', NULL, '2025-09-05 23:47:37', '2025-09-05 23:47:37', 'kmccullough', 'student', 'inactive', NULL, 'Kurtis', NULL, 'McCullough', 'male', '1997-05-13', 'Venezuela', '8106658493', NULL, '804-534-9329', NULL, '700 Guadalupe Stravenue, East Kavonstad, OK 63578-6089', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Regan Yost', NULL, '1-775-240-5878', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (104, 'Audie Rae Schuppe', 'lang.lamar@example.net', '2025-09-05 23:47:38', '$2y$12$Wb6xS2a4m9eJjrQDA/yAsODpAikqdUwLT8t3Njsz/nkZZTQueIOcm', NULL, '2025-09-05 23:47:38', '2025-09-05 23:47:38', 'aschuppe', 'student', 'inactive', NULL, 'Audie', 'Rae', 'Schuppe', 'male', '2001-05-20', 'Guinea-Bissau', '2388366005', NULL, '+1.845.814.3959', NULL, '1788 Brielle Ridges Suite 297, North Madge, OR 37029-5306', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dr. Lucio Lueilwitz III', NULL, '1-820-472-7041', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (108, 'Syble  Fahey', 'shaina.auer@example.com', '2025-09-05 23:47:39', '$2y$12$.0n9/ziEwTIHRb9atSOZ2.4kk.E4NvKgAfVLyMv061jD/TKgQpe06', NULL, '2025-09-05 23:47:39', '2025-09-05 23:47:39', 'sfahey', 'student', 'inactive', NULL, 'Syble', NULL, 'Fahey', 'male', '2003-11-26', 'Albania', '4789850254', NULL, '+1-856-552-0639', NULL, '7327 Balistreri Port Suite 582, Lemkeshire, AL 18571-9710', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Layne Block', NULL, '(971) 427-8858', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (109, 'Cydney Birdie Carter', 'rowland16@example.com', '2025-09-05 23:47:39', '$2y$12$QNWOaTRPUFx2Y9aWBjp2C.J2MCD3MuiaQyycymmDngqyZXWo73aaC', NULL, '2025-09-05 23:47:39', '2025-09-05 23:47:39', 'ccarter', 'student', 'active', NULL, 'Cydney', 'Birdie', 'Carter', 'male', '2005-06-21', 'Brazil', '1919955846', NULL, '331-743-3441', NULL, '35806 Paucek Mountains Apt. 923, Lake Cleoland, FL 27645', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dejah Skiles', NULL, '(747) 324-6674', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (110, 'Bruce Guiseppe Renner', 'carter.houston@example.org', '2025-09-05 23:47:40', '$2y$12$0tIixni3Cl3vL/eeNRhIv.j9edO7Vbs.5o3cdB3zv/m8H0zRyikrC', NULL, '2025-09-05 23:47:40', '2025-09-05 23:47:40', 'brenner', 'student', 'inactive', NULL, 'Bruce', 'Guiseppe', 'Renner', 'male', '1999-07-12', 'Algeria', '1009120260', NULL, '1-251-615-7723', NULL, '9199 Olga Lakes, Grantborough, NV 83688-5278', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Nicola Zieme', NULL, '1-959-437-9428', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (112, 'Alfred Guiseppe Jerde', 'xhoppe@example.org', '2025-09-05 23:47:40', '$2y$12$RHxwgDRg2b2WuaDpMphS0OokZJCmKOjIuTrhm6fb1bY0tViiVv4rK', NULL, '2025-09-05 23:47:40', '2025-09-05 23:47:40', 'ajerde', 'student', 'active', NULL, 'Alfred', 'Guiseppe', 'Jerde', 'male', '2006-01-05', 'Mali', '2085372654', NULL, '610.486.1325', NULL, '584 Maxie Haven, Ethelview, GA 64035', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Helene Medhurst', NULL, '773.332.4974', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (114, 'Anna Aubrey Rutherford', 'adelia55@example.net', '2025-09-05 23:47:41', '$2y$12$RFlRiJTGNNNoImcZscFbEOTQLrOWyg1sVJPnqz7QjFvTGo775iNvO', NULL, '2025-09-05 23:47:41', '2025-09-05 23:47:41', 'arutherford', 'student', 'active', NULL, 'Anna', 'Aubrey', 'Rutherford', 'male', '2006-05-03', 'San Marino', '4242571916', NULL, '+1 (304) 784-1469', NULL, '3867 Devan Mountains, Halvorsonmouth, UT 42956', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Lew Towne', NULL, '(320) 435-1878', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (116, 'Maeve Madge Thiel', 'maximillian.miller@example.net', '2025-09-05 23:47:41', '$2y$12$vv5mlA/xq0JX95BjZfasvOIz2VgTjys0LAIeVVcfmHSKkHEgckJZS', NULL, '2025-09-05 23:47:41', '2025-09-05 23:47:41', 'mthiel', 'student', 'suspended', NULL, 'Maeve', 'Madge', 'Thiel', 'male', '2001-01-04', 'Sweden', '5005309269', NULL, '+1-646-985-6119', NULL, '69549 Hegmann Circles, Hyattmouth, AR 10541', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Caterina Wyman', NULL, '+1.678.899.6770', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (125, 'Kennedy Vivienne Marvin', 'shaun.murphy@example.org', '2025-09-05 23:47:44', '$2y$12$Gd7ultc18AXQZisQLgcyv.zveUmvqLEpGqr9nDHvctqh2/2H3zrAm', NULL, '2025-09-05 23:47:44', '2025-09-05 23:47:44', 'kmarvin', 'student', 'active', NULL, 'Kennedy', 'Vivienne', 'Marvin', 'male', '1996-01-05', 'Guadeloupe', '4281888639', NULL, '+1 (640) 333-9259', NULL, '38248 Myles Ports, East Dudley, HI 42050-8589', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mr. Brycen Zboncak Sr.', NULL, '+1-229-819-7735', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (129, 'Cornelius Icie Heidenreich', 'jevon.quitzon@example.com', '2025-09-05 23:47:45', '$2y$12$LQJ8WrAD3tS87.4EEduNL.ot3g3jCR3qdWHS9mvMmHJ7c.BF6N.0G', NULL, '2025-09-05 23:47:45', '2025-09-05 23:47:45', 'cheidenreich', 'student', 'active', NULL, 'Cornelius', 'Icie', 'Heidenreich', 'male', '1996-08-24', 'Qatar', '6317787874', NULL, '223.827.5140', NULL, '62878 Padberg Oval, Robelmouth, NH 12133', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Lexie Gusikowski', NULL, '520-593-5656', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (130, 'Stewart Helen Paucek', 'bstehr@example.org', '2025-09-05 23:47:45', '$2y$12$DKv1Gdc1nmRzQjzA1GPwHu0QPaIdNyu20yKkpwIki3roa5tRzg12y', NULL, '2025-09-05 23:47:45', '2025-09-05 23:47:45', 'spaucek', 'student', 'active', NULL, 'Stewart', 'Helen', 'Paucek', 'male', '2000-07-31', 'Antarctica (the territory South of 60 deg S)', '1499801581', NULL, '1-815-479-7279', NULL, '65281 Reilly Mills Suite 765, Gloriatown, IL 14536-4672', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Corine Greenholt', NULL, '(978) 783-9388', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (134, 'Hans Rebeka Flatley', 'aleen.mclaughlin@example.com', '2025-09-05 23:47:46', '$2y$12$Jyq6ktschdjcjpLHKM5WMOzN3x1KGdGjxl/k74Sbs2qI5pBhULNg.', NULL, '2025-09-05 23:47:46', '2025-09-05 23:47:46', 'hflatley', 'student', 'suspended', NULL, 'Hans', 'Rebeka', 'Flatley', 'male', '2006-10-18', 'Jamaica', '9850124024', NULL, '(424) 966-3207', NULL, '63324 Elliot Field Suite 031, Devonberg, MA 78106-0121', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rachelle Schoen V', NULL, '+1-463-480-3922', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (136, 'Birdie Beau Wuckert', 'kitty.franecki@example.com', '2025-09-05 23:47:47', '$2y$12$Bps8zVALICzcuPFFvsdACO4DD7ygmv.1Q28tbRSxdekjmvCafRo1e', NULL, '2025-09-05 23:47:47', '2025-09-05 23:47:47', 'bwuckert', 'student', 'active', NULL, 'Birdie', 'Beau', 'Wuckert', 'male', '2005-05-16', 'Macedonia', '0780215782', NULL, '(317) 353-1810', NULL, '48125 Okuneva Circles Suite 461, Elmiraton, ME 57612-9580', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ms. Mercedes Upton IV', NULL, '+18569254888', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (139, 'Leilani Marianne Russel', 'ohowell@example.org', '2025-09-05 23:47:48', '$2y$12$HY.6Af7qn.UVAzFe9xNAreKT3OOrIcodc8waoj.jQJRd1U0GSIxBK', NULL, '2025-09-05 23:47:48', '2025-09-05 23:47:48', 'lrussel', 'student', 'inactive', NULL, 'Leilani', 'Marianne', 'Russel', 'male', '1997-11-09', 'Malta', '2300709996', NULL, '(303) 786-2274', NULL, '8950 Kreiger Views Apt. 836, Lake Josephland, DE 53305-5077', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kaylee Borer DVM', NULL, '337-266-5762', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (55, 'Abe Liliane Funk', 'emmy05@example.org', '2025-09-05 23:47:25', '$2y$12$JGnTU3KYheTg6VDvoHZ/DeWFk2Q0LC.0xwd6geTtqGfbVceRZ61De', NULL, '2025-09-05 23:47:25', '2025-09-05 23:47:25', 'afunk', 'student', 'suspended', NULL, 'Abe', 'Liliane', 'Funk', 'female', '2002-09-21', 'Azerbaijan', '4244964343', NULL, '(660) 538-3839', NULL, '251 Hahn Springs Suite 807, Aufderharton, WV 41116', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rebekah Purdy III', NULL, '1-223-964-9380', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (57, 'Maria Isabel Garcia', 'maria.garcia@university.edu', '2025-09-05 23:47:26', '$2y$12$EhmEK16mkxPVj6UeqFXXve7MEpeqcMQ6dHyx0x1UXytpDu2iFni8e', NULL, '2025-09-05 23:47:26', '2025-09-05 23:47:26', 'mgarcia', 'student', 'active', NULL, 'Maria', 'Isabel', 'Garcia', 'female', '2000-07-22', 'Spanish', '111222333', NULL, '+1234567900', NULL, '777 Campus Way, New York, NY 10004', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ana Garcia', NULL, '+34612345679', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (59, 'Zakary Ruthie Miller', 'miller52@example.net', '2025-09-05 23:47:27', '$2y$12$.chIt/WRLxXXzzO8lg1b9./bC48caFs0zFhq7jSK1DeYWHEnBeSJm', NULL, '2025-09-05 23:47:27', '2025-09-05 23:47:27', 'zmiller', 'student', 'active', NULL, 'Zakary', 'Ruthie', 'Miller', 'female', '2005-12-24', 'Spain', '9267032153', NULL, '+1-743-481-4192', NULL, '67121 Ethelyn Pine, North Raymondview, IL 79976-4468', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mr. Nikko Nienow', NULL, '+1 (334) 769-5090', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (60, 'Jane Elizabeth Smith', 'jane.smith@university.edu', '2025-09-05 23:47:27', '$2y$12$S7wrc3G9UOOyMApvDAiWoOG1Qx30cJYKSFDnseZhVFXWOmAE4mo/a', NULL, '2025-09-05 23:47:27', '2025-09-05 23:47:27', 'jsmith', 'student', 'active', NULL, 'Jane', 'Elizabeth', 'Smith', 'female', '1999-05-20', 'Canadian', '987654321', NULL, '+1234567894', NULL, '789 College Ave, New York, NY 10002', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Robert Smith', NULL, '+1234567895', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (65, 'Kade  Hessel', 'omraz@example.com', '2025-09-05 23:47:28', '$2y$12$OavPd9iPD85Xu0sTDZQsr.CORfOO9O8/cyzCzojBntvaoFAdj.rUO', NULL, '2025-09-05 23:47:28', '2025-09-05 23:47:28', 'khessel', 'student', 'active', NULL, 'Kade', NULL, 'Hessel', 'female', '2000-12-28', 'Saint Helena', '1189000641', NULL, '865.462.2464', NULL, '348 Carter Forks, Simonisview, NC 45808-3095', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Valerie Boyle', NULL, '+1-276-404-3123', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (68, 'Lelia Graciela Pollich', 'bessie.kulas@example.com', '2025-09-05 23:47:29', '$2y$12$OcPvC7IVEULjikCcXE1rnOD7Au0M0M4CWHSgu0BpT/zaTVssx94zm', NULL, '2025-09-05 23:47:29', '2025-09-05 23:47:29', 'lpollich', 'student', 'suspended', NULL, 'Lelia', 'Graciela', 'Pollich', 'female', '2003-02-06', 'Romania', '0291518712', NULL, '+1-586-277-7696', NULL, '1203 Denesik Fork, Port Alec, SC 61174', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mr. Erik Connelly PhD', NULL, '870-418-1781', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (69, 'Nasir  O''Connell', 'hills.monroe@example.org', '2025-09-05 23:47:29', '$2y$12$eKW9kV323LXo9p3QsKrXweFEzekSFm1iMYGdBgwt6MIrelpNroX8a', NULL, '2025-09-05 23:47:29', '2025-09-05 23:47:29', 'noconnell', 'student', 'active', NULL, 'Nasir', NULL, 'O''Connell', 'female', '2007-02-01', 'Greenland', '1094227469', NULL, '518.954.6252', NULL, '489 Conroy Court, East Haileeville, GA 93710-1268', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Elvis Ward V', NULL, '+1 (860) 707-2145', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (73, 'Jailyn Patrick Grant', 'sandrine.koss@example.com', '2025-09-05 23:47:30', '$2y$12$kfD0LuwoMIuoBHDwe7n4reUqLasAzsG2FoSsD/gIJVXIOf8IXCyd.', NULL, '2025-09-05 23:47:30', '2025-09-05 23:47:30', 'jgrant', 'student', 'suspended', NULL, 'Jailyn', 'Patrick', 'Grant', 'female', '2005-04-18', 'Pitcairn Islands', '7419857775', NULL, '551.706.3896', NULL, '634 Smitham Camp Suite 242, Broderickview, TX 58592', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Prof. Abelardo Wolff', NULL, '+1 (270) 667-7271', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (77, 'Leanne Jaida Crist', 'effertz.bernard@example.net', '2025-09-05 23:47:31', '$2y$12$jRIBPS7zatXkbeAC7X8PmeKDwR7LvFIuldFWPGHWy6pm754RS7kPO', NULL, '2025-09-05 23:47:31', '2025-09-05 23:47:31', 'lcrist', 'student', 'active', NULL, 'Leanne', 'Jaida', 'Crist', 'female', '1997-03-27', 'Nauru', '4803407969', NULL, '1-580-674-8467', NULL, '857 Dooley Hill, Rogahnfurt, AK 16039', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mr. Dean Stracke DVM', NULL, '+1 (915) 708-5478', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (84, 'Lyric Bette Terry', 'schroeder.richie@example.org', '2025-09-05 23:47:33', '$2y$12$nky.pHQH7vVzyObMK1mSte/hMrDZ10u08/sYBw/4s8W0tnHKHCSFK', NULL, '2025-09-05 23:47:33', '2025-09-05 23:47:33', 'lterry', 'student', 'active', NULL, 'Lyric', 'Bette', 'Terry', 'female', '1999-02-20', 'Lesotho', '0353767606', NULL, '+1-859-389-0344', NULL, '29071 Andres Mall, Farrellbury, CT 90631-0036', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Celestino Osinski DVM', NULL, '(559) 901-9957', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (86, 'Micaela Wilber Koss', 'jon72@example.org', '2025-09-05 23:47:33', '$2y$12$yQZa0n9ytjjLjvUGJYqovOK5ngl8vtH6b6uuzoA8Sdz.TB4zmiEma', NULL, '2025-09-05 23:47:33', '2025-09-05 23:47:33', 'mkoss', 'student', 'active', NULL, 'Micaela', 'Wilber', 'Koss', 'female', '1997-09-28', 'Tajikistan', '2802287434', NULL, '(351) 312-5494', NULL, '799 Emmanuelle Gardens, Port Kiley, SD 70445', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Stacey Lemke', NULL, '774-681-9644', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (88, 'Selmer Rowena Murray', 'lane85@example.net', '2025-09-05 23:47:34', '$2y$12$9kNNWtAIR6ogMM1MXbWDVus9CpVOhpMl1hWubjGHoAKzC0hIoe0.S', NULL, '2025-09-05 23:47:34', '2025-09-05 23:47:34', 'smurray', 'student', 'active', NULL, 'Selmer', 'Rowena', 'Murray', 'female', '2003-07-14', 'British Virgin Islands', '8220594232', NULL, '+1.484.665.4829', NULL, '26136 Feil Spurs, North Wyatt, OK 37895-1303', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Prof. Heloise Zulauf DVM', NULL, '+1-304-717-1391', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (93, 'Rosalind Clifford Brown', 'stokes.heloise@example.net', '2025-09-05 23:47:35', '$2y$12$1HFam.iYyBzD4ZuzdCoA.Oq3jzN4TeQL9UArRHj8GI/S1iOEDudam', NULL, '2025-09-05 23:47:35', '2025-09-05 23:47:35', 'rbrown', 'student', 'inactive', NULL, 'Rosalind', 'Clifford', 'Brown', 'female', '2005-03-25', 'Slovenia', '3977241702', NULL, '+1-708-612-2636', NULL, '718 Camron Walk Apt. 049, Terrellmouth, NE 99496-0303', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Leilani Stokes Sr.', NULL, '828-538-5972', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (94, 'Lexi  Swaniawski', 'ollie.brown@example.net', '2025-09-05 23:47:35', '$2y$12$PEhGgN1RpT1Db5ab/wcW6ebS0L4DQWlxANJ7mcht46jUbBOd/7J.G', NULL, '2025-09-05 23:47:35', '2025-09-05 23:47:35', 'lswaniawski', 'student', 'active', NULL, 'Lexi', NULL, 'Swaniawski', 'female', '2005-07-18', 'Bahrain', '8842236695', NULL, '914.899.2858', NULL, '73052 Beer Stream, Jazminfort, WA 33634', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'June Murazik', NULL, '1-651-813-6083', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (95, 'Turner Audie Schinner', 'qzulauf@example.org', '2025-09-05 23:47:36', '$2y$12$8I4zC.WxR/Nf7M3qOe6v8eBq9MTMWQiJDdfmhUomVcdHARi8AG5S.', NULL, '2025-09-05 23:47:36', '2025-09-05 23:47:36', 'tschinner', 'student', 'active', NULL, 'Turner', 'Audie', 'Schinner', 'female', '2005-04-20', 'Antarctica (the territory South of 60 deg S)', '6585149670', NULL, '+1-651-676-6205', NULL, '595 Joseph Haven Apt. 545, North Dougburgh, UT 37891', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Columbus Kuhlman', NULL, '469.215.5266', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (96, 'Shyann Rosalinda Toy', 'skye.ward@example.org', '2025-09-05 23:47:36', '$2y$12$5QgYZV7G3VBHWZsc.CPkPunrSeqwrZ.iPnN4MAGqvyYFCTZTTXt6O', NULL, '2025-09-05 23:47:36', '2025-09-05 23:47:36', 'stoy', 'student', 'active', NULL, 'Shyann', 'Rosalinda', 'Toy', 'female', '2006-10-20', 'Palau', '9077791249', NULL, '(979) 466-2941', NULL, '84670 Cullen Ports, Verdatown, WI 09731-5649', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dr. Kenny Funk DVM', NULL, '+12344487500', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (98, 'Nicholaus Roxanne Bednar', 'kirk66@example.com', '2025-09-05 23:47:37', '$2y$12$UQJTdlqjfAsqrN3GpI0vL.9SJ77EkT1c33BTUtMkmG0Gdu5BDHa2C', NULL, '2025-09-05 23:47:37', '2025-09-05 23:47:37', 'nbednar', 'student', 'active', NULL, 'Nicholaus', 'Roxanne', 'Bednar', 'female', '2000-12-05', 'Puerto Rico', '6589656088', NULL, '+1.276.634.7057', NULL, '13652 Arianna Pass, Nikkoview, NY 30516-9137', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kameron Lakin DDS', NULL, '+1-386-686-4374', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (111, 'Frank  Howe', 'cassin.yvonne@example.com', '2025-09-05 23:47:40', '$2y$12$RwO2gRXOZ1cNUvYu2q2sL.3wZmp3WwnZAn./J3qTTZE2DePAOMbp6', NULL, '2025-09-05 23:47:40', '2025-09-05 23:47:40', 'fhowe', 'student', 'inactive', NULL, 'Frank', NULL, 'Howe', 'female', '2006-03-03', 'Andorra', '3449636548', NULL, '272-631-4294', NULL, '718 Gleason Rapid, New Lydia, GA 16901', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jamey Denesik', NULL, '+1 (469) 499-7191', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (117, 'Kiarra Theodore Roob', 'gerard12@example.org', '2025-09-05 23:47:41', '$2y$12$HfeG/yQU6wsbvzv9F47xuOQSfaVDr8IDVvZLkPYfgmYKO4tD/cSRO', NULL, '2025-09-05 23:47:41', '2025-09-05 23:47:41', 'kroob', 'student', 'active', NULL, 'Kiarra', 'Theodore', 'Roob', 'female', '2000-01-01', 'Oman', '7376033458', NULL, '1-940-221-6961', NULL, '120 Iliana Oval Apt. 378, Spinkafurt, MA 09801-2775', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kelton Schaefer', NULL, '828-202-8090', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (118, 'Gino Heber Mitchell', 'brant66@example.net', '2025-09-05 23:47:42', '$2y$12$CnTV6rOJpiv9Hb5XtKX9m.OIl49o15egeSJC6ik9SN/OhiX/e1Oc2', NULL, '2025-09-05 23:47:42', '2025-09-05 23:47:42', 'gmitchell', 'student', 'suspended', NULL, 'Gino', 'Heber', 'Mitchell', 'female', '1997-04-06', 'Congo', '5769872413', NULL, '272-575-1255', NULL, '275 Ray Haven, Port Celia, KY 85457', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Prof. Linnea Mayert DVM', NULL, '+1-276-740-3507', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (119, 'Briana Kennith Leffler', 'xbrekke@example.net', '2025-09-05 23:47:42', '$2y$12$MdcaNX1srY6hFlntKXN7QeAkCRp2wP6Hfg7jb8J3ujthC6Fhr/Xn2', NULL, '2025-09-05 23:47:42', '2025-09-05 23:47:42', 'bleffler', 'student', 'active', NULL, 'Briana', 'Kennith', 'Leffler', 'female', '2003-02-17', 'Antigua and Barbuda', '9395893227', NULL, '(682) 852-0575', NULL, '669 Fay Wells Apt. 519, Hubertbury, NH 27658', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Charlene Schmeler', NULL, '+1.626.899.9854', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (120, 'Triston April Botsford', 'elenora.beatty@example.org', '2025-09-05 23:47:42', '$2y$12$0JbEXxYKIWd0chL13QPqmuZfIiqk/EJF0czJZTyd.786eLDCYpLkm', NULL, '2025-09-05 23:47:42', '2025-09-05 23:47:42', 'tbotsford', 'student', 'inactive', NULL, 'Triston', 'April', 'Botsford', 'female', '2006-02-09', 'Andorra', '6129004214', NULL, '838-621-4793', NULL, '439 Pansy Prairie Suite 711, Lake Enosside, HI 26132', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Summer Lueilwitz', NULL, '1-650-253-9817', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (123, 'Kamryn Jason Stoltenberg', 'vickie51@example.net', '2025-09-05 23:47:43', '$2y$12$oOdxedk6nsAfpw0Z9ky0BeFqsFvvpWKSXXnGqkaKORme9BDj8M8su', NULL, '2025-09-05 23:47:43', '2025-09-05 23:47:43', 'kstoltenberg', 'student', 'active', NULL, 'Kamryn', 'Jason', 'Stoltenberg', 'female', '1997-10-29', 'Mali', '3957708114', NULL, '541-685-6923', NULL, '28727 Wisoky Parkways, West Rachelleburgh, UT 92973-4563', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Prof. Gianni Heathcote Jr.', NULL, '+1-806-996-9454', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (124, 'Agustin Sharon Halvorson', 'fabernathy@example.net', '2025-09-05 23:47:44', '$2y$12$rladJgm5VSd1.epXQnFBju3zYIMHxTLV/SBKMXV4MZFF/qL4qYbLy', NULL, '2025-09-05 23:47:44', '2025-09-05 23:47:44', 'ahalvorson', 'student', 'active', NULL, 'Agustin', 'Sharon', 'Halvorson', 'female', '1997-12-13', 'Myanmar', '0496485491', NULL, '1-304-730-4844', NULL, '8935 Kunze Fort, Keelingmouth, WI 89545-0717', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mr. Gerald O''Hara', NULL, '845.790.1257', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (126, 'Agnes Ariane Schinner', 'maybelle.spencer@example.org', '2025-09-05 23:47:44', '$2y$12$8NMgVO5P6Z0JnM7VG2Nep.330igaZfo/F.dwu6f5nmx4PEgZTJY7G', NULL, '2025-09-05 23:47:44', '2025-09-05 23:47:44', 'aschinner', 'student', 'inactive', NULL, 'Agnes', 'Ariane', 'Schinner', 'female', '2001-03-28', 'Finland', '9413756505', NULL, '828.436.8559', NULL, '51500 Farrell Hill Apt. 467, West Brookchester, ID 73016', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jeanette Wilkinson', NULL, '475.733.2622', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (128, 'Lillie Arvel Crona', 'vinnie.murray@example.com', '2025-09-05 23:47:45', '$2y$12$FUn/5GV55lW510uC2QMrZeimHRhC7SCIQ6FIRDPch3cQ1mJP1i/Yi', NULL, '2025-09-05 23:47:45', '2025-09-05 23:47:45', 'lcrona', 'student', 'suspended', NULL, 'Lillie', 'Arvel', 'Crona', 'female', '2006-02-07', 'Congo', '6175778448', NULL, '1-303-941-1933', NULL, '4941 Kelli Mill, Krystinatown, DE 13355', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Elbert Miller', NULL, '1-424-893-0794', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (133, 'Herta Bill Prohaska', 'luisa.jaskolski@example.org', '2025-09-05 23:47:46', '$2y$12$gQY5E6gi8dQE6o3QfT9omOGETxSHeHJF4CU8RmHHupBmeLNDAFxse', NULL, '2025-09-05 23:47:46', '2025-09-05 23:47:46', 'hprohaska', 'student', 'active', NULL, 'Herta', 'Bill', 'Prohaska', 'female', '2004-07-04', 'New Zealand', '5667997858', NULL, '1-708-380-4192', NULL, '15937 Erich Neck Suite 934, Muellerside, WI 47671-8850', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mrs. Elvera Little', NULL, '423.742.2110', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (138, 'Nina Weston Hirthe', 'isaiah.hettinger@example.com', '2025-09-05 23:47:47', '$2y$12$JckQDSPqtMn6OP9NnVGskuWYUyjstUCJ.osAQrwWD9cFxcUWvFuiy', NULL, '2025-09-05 23:47:47', '2025-09-05 23:47:47', 'nhirthe', 'student', 'inactive', NULL, 'Nina', 'Weston', 'Hirthe', 'female', '2007-06-13', 'Bangladesh', '6217622281', NULL, '(929) 807-1692', NULL, '8321 Lisette Hills Apt. 807, Emmanuelleshire, DC 92578-7630', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Hester Turner', NULL, '+1 (689) 889-6995', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (144, 'Jacynthe Adelbert Hoeger', 'rowena.rippin@example.com', '2025-09-05 23:47:49', '$2y$12$jTey99OAxPVsl7QKyugsbuNaNHJ/Wz8Flz8ONcBfpAoCFFIlpY.bO', NULL, '2025-09-05 23:47:49', '2025-09-05 23:47:49', 'jhoeger', 'student', 'inactive', NULL, 'Jacynthe', 'Adelbert', 'Hoeger', 'female', '2005-04-15', 'Djibouti', '8510509846', NULL, '1-701-435-9326', NULL, '81674 Vernice Greens Suite 413, Lake Rickiestad, IL 71063-7138', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Darius Fahey', NULL, '1-669-269-4920', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (148, 'Cecelia Destinee Murazik', 'turcotte.marcia@example.net', '2025-09-05 23:47:50', '$2y$12$8hHuFi9/IAiDLvPixH.Hy.JZgOtrfiA8BO3Hd/.x8gdUQbmfpz6hi', NULL, '2025-09-05 23:47:50', '2025-09-05 23:47:50', 'cmurazik', 'student', 'active', NULL, 'Cecelia', 'Destinee', 'Murazik', 'female', '1998-05-19', 'Costa Rica', '0773266787', NULL, '714.309.7087', NULL, '8760 Murphy Rapid, Lake Willieberg, IN 57295-8708', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Luther Pouros', NULL, '(608) 476-5796', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (149, 'Concepcion  Shields', 'karina76@example.com', '2025-09-05 23:47:50', '$2y$12$yojm7JThXBgQRqrzGq9y/Om2M5hkSlYY41n67vKUkbvtY7lpkc6V2', NULL, '2025-09-05 23:47:50', '2025-09-05 23:47:50', 'cshields', 'student', 'inactive', NULL, 'Concepcion', NULL, 'Shields', 'female', '2005-03-24', 'Tajikistan', '6750313205', NULL, '+1.938.588.3583', NULL, '92406 Kelli Plaza, Watsicaberg, MN 40990', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alan Mayert', NULL, '262-656-9577', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (153, 'Melvin Lula Orn', 'isaac.runolfsdottir@example.org', '2025-09-05 23:47:51', '$2y$12$F.MW09C8PBtNICslgJhsEe3VFIA8VQy8eVcrJHJxX7G9HHL0lA65m', NULL, '2025-09-05 23:47:51', '2025-09-05 23:47:51', 'morn', 'student', 'active', NULL, 'Melvin', 'Lula', 'Orn', 'female', '2000-01-07', 'Lao People''s Democratic Republic', '7975198815', NULL, '515-320-0858', NULL, '934 Schaefer Estates, East Oswaldside, UT 20304', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jevon Leannon', NULL, '+17702459472', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (154, 'Elise Sister Nikolaus', 'white.ambrose@example.com', '2025-09-05 23:47:51', '$2y$12$glPOS186QFMdCtDy6GAbT.upvoVArD6wPZF61kM7JI.W618de3U9a', NULL, '2025-09-05 23:47:51', '2025-09-05 23:47:51', 'enikolaus', 'student', 'suspended', NULL, 'Elise', 'Sister', 'Nikolaus', 'female', '2001-11-28', 'Czech Republic', '7372732457', NULL, '+16314593714', NULL, '7079 Raynor Street, Port Ronaldo, ME 75440', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Omari Schmitt', NULL, '+1-415-237-6718', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (61, 'Jared Gaston Bauch', 'bullrich@example.com', '2025-09-05 23:47:27', '$2y$12$0bLAiZj6LthbRFDiARRRo.eEwEF4MTsnEslaSng3OUgPp1DAt/yVe', NULL, '2025-09-05 23:47:27', '2025-09-05 23:47:27', 'jbauch', 'student', 'active', NULL, 'Jared', 'Gaston', 'Bauch', 'other', '2001-06-17', 'British Virgin Islands', '3663002467', NULL, '920.556.1137', NULL, '927 Rippin Trail Apt. 497, Bartellchester, IN 59135-2110', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Laurel Gutmann', NULL, '208-853-5130', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (64, 'Michale  Gerlach', 'nicole.marvin@example.org', '2025-09-05 23:47:28', '$2y$12$nfI8c6gCHsqRAF8LytPxeO6oju0PXskwPWSubXlvGvtETXmtztumu', NULL, '2025-09-05 23:47:28', '2025-09-05 23:47:28', 'mgerlach', 'student', 'suspended', NULL, 'Michale', NULL, 'Gerlach', 'other', '2000-10-04', 'Belarus', '5040896826', NULL, '657-399-5503', NULL, '934 Jeffery Place Apt. 114, Karastad, ID 44919-3793', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mona Will', NULL, '323.220.9344', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (67, 'Amir  Ziemann', 'jarrett.gleason@example.net', '2025-09-05 23:47:28', '$2y$12$0ZLKHeNRNbXd2cWiwY6/5uI9vHldVPVmqb8RR6sRhTLOlEtcLV7dK', NULL, '2025-09-05 23:47:28', '2025-09-05 23:47:28', 'aziemann', 'student', 'active', NULL, 'Amir', NULL, 'Ziemann', 'other', '1999-04-11', 'Antigua and Barbuda', '6630667242', NULL, '+1.332.625.3310', NULL, '1060 Haag Mission Apt. 197, Schneiderfort, MD 26804', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rae Durgan', NULL, '1-930-986-9225', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (71, 'Pete  Beier', 'lew22@example.org', '2025-09-05 23:47:29', '$2y$12$91jjy7BcyPERpHb4w9DcFee1UMayrkWHs8y4idT6aPKJ6r6msZKVu', NULL, '2025-09-05 23:47:29', '2025-09-05 23:47:29', 'pbeier', 'student', 'active', NULL, 'Pete', NULL, 'Beier', 'other', '2001-10-29', 'Saint Kitts and Nevis', '0934380552', NULL, '(352) 319-5030', NULL, '79126 Leanne Via, Carloschester, DE 12005-8885', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alejandra Wuckert', NULL, '732-560-5151', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (75, 'Dianna  Gleason', 'boyer.agustin@example.org', '2025-09-05 23:47:30', '$2y$12$QMH408jgHGIYhrgjtch9FuoTHj6y366ClYQDqH0UpAputWQSRkq2K', NULL, '2025-09-05 23:47:30', '2025-09-05 23:47:30', 'dgleason', 'student', 'suspended', NULL, 'Dianna', NULL, 'Gleason', 'other', '2006-05-23', 'Grenada', '4155956695', NULL, '(864) 475-8040', NULL, '205 Verona Trafficway Suite 858, Stephonfort, IA 60188', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Laverna Buckridge', NULL, '1-571-857-8242', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (78, 'Lesley Ozella Ferry', 'kling.francisco@example.com', '2025-09-05 23:47:31', '$2y$12$AGrkj5fMPIqwNC1/UdqZ5OyYDzh0OuK78L2tIWiWkPNKMYpdujLwK', NULL, '2025-09-05 23:47:31', '2025-09-05 23:47:31', 'lferry', 'student', 'active', NULL, 'Lesley', 'Ozella', 'Ferry', 'other', '1996-01-11', 'Wallis and Futuna', '3899453290', NULL, '+1-820-850-3987', NULL, '67249 Verla Plain Suite 885, North Titus, MT 05003', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bruce Cole', NULL, '209-970-6490', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (79, 'Dallin Tressa Gibson', 'hodkiewicz.susana@example.com', '2025-09-05 23:47:31', '$2y$12$04oSeXQBIAFuBb.Ef0aeUeeDcOj0n4vzbb8o8DV9Y08TkKLfKsg1W', NULL, '2025-09-05 23:47:31', '2025-09-05 23:47:31', 'dgibson', 'student', 'suspended', NULL, 'Dallin', 'Tressa', 'Gibson', 'other', '2006-05-12', 'Suriname', '5653832875', NULL, '1-413-725-0851', NULL, '8131 Natalie Alley Suite 977, Josianeburgh, DE 77462', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Leopold Dibbert', NULL, '870-475-2809', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (82, 'Queen Michel Eichmann', 'kasey.predovic@example.net', '2025-09-05 23:47:32', '$2y$12$kALQ4ZTzwqPYuCvSdJJSteDpOSAPUwun9tubqs2xOW0oiZHwUETHe', NULL, '2025-09-05 23:47:32', '2025-09-05 23:47:32', 'qeichmann', 'student', 'active', NULL, 'Queen', 'Michel', 'Eichmann', 'other', '2007-03-13', 'Hungary', '9601946050', NULL, '716-752-2716', NULL, '36878 Brendan Forge, Lizziebury, WY 31437-4273', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mrs. Theodora Turcotte', NULL, '364-223-5168', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (83, 'Lacy  Senger', 'krajcik.emilio@example.com', '2025-09-05 23:47:32', '$2y$12$Uzgi5XK7pAX5qKFEuBeSKO3uEViQlVrCrmQa3yWa82xknLGtBB4.2', NULL, '2025-09-05 23:47:32', '2025-09-05 23:47:32', 'lsenger', 'student', 'inactive', NULL, 'Lacy', NULL, 'Senger', 'other', '1998-01-03', 'Wallis and Futuna', '3125578172', NULL, '1-919-517-3357', NULL, '3337 Jeramie Track Suite 486, New Hulda, NV 36985-0181', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ms. Maureen Denesik Sr.', NULL, '+1-951-266-2111', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (85, 'Demetrius Ward Paucek', 'kianna54@example.com', '2025-09-05 23:47:33', '$2y$12$zmDgXPLY/YzAJvHT1ku32u5w7urmE7mdgivIH6Inuax7H4pToI67q', NULL, '2025-09-05 23:47:33', '2025-09-05 23:47:33', 'dpaucek', 'student', 'active', NULL, 'Demetrius', 'Ward', 'Paucek', 'other', '1997-12-04', 'Bangladesh', '4612583563', NULL, '+1-313-699-5153', NULL, '179 Kautzer Extensions, New Josiah, LA 48842-5705', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alanis Schneider II', NULL, '669-342-8653', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (87, 'Ottilie  Flatley', 'tiffany37@example.com', '2025-09-05 23:47:33', '$2y$12$r7e7xz3FAqbvF3l0kZzhXuQ//Kq9w1NF5BHDe5Ljr4MjgR//rbVPm', NULL, '2025-09-05 23:47:33', '2025-09-05 23:47:33', 'oflatley', 'student', 'active', NULL, 'Ottilie', NULL, 'Flatley', 'other', '2000-10-25', 'China', '2937572648', NULL, '503-645-2185', NULL, '507 Maida Underpass, New Ciara, OK 60177', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Prof. Jacinto Yundt', NULL, '+1 (859) 755-5192', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (91, 'Edmond Triston Kassulke', 'ward.woodrow@example.com', '2025-09-05 23:47:35', '$2y$12$2pVr0tsccaywtNencNfTru8v2bcyWzDXMteklU46yJc/xTt3CQCOG', NULL, '2025-09-05 23:47:35', '2025-09-05 23:47:35', 'ekassulke', 'student', 'active', NULL, 'Edmond', 'Triston', 'Kassulke', 'other', '2000-04-21', 'Romania', '3292055766', NULL, '364.614.4369', NULL, '655 Elroy Square Suite 359, Gleichnerborough, OK 62207-3091', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Prof. Raoul Auer PhD', NULL, '(667) 518-3974', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (97, 'Dorris Velda Kutch', 'ujakubowski@example.com', '2025-09-05 23:47:36', '$2y$12$kFNwDNcMtQ.t5DooayOfLeI/y.eHGcpJebNvDqUVf1oIuUUolcNQO', NULL, '2025-09-05 23:47:36', '2025-09-05 23:47:36', 'dkutch', 'student', 'inactive', NULL, 'Dorris', 'Velda', 'Kutch', 'other', '2003-08-21', 'Vanuatu', '6923098035', NULL, '1-779-846-8275', NULL, '411 Farrell Rue Apt. 845, Camyllechester, VA 84723', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Elroy Schaden', NULL, '413-938-9884', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (101, 'Chandler Adriel Hoppe', 'stevie.oberbrunner@example.org', '2025-09-05 23:47:37', '$2y$12$o8jTVK9zASIETKlASMW8GOTwggyR/j1O5VNoVddrWvJx0snAVTSoW', NULL, '2025-09-05 23:47:37', '2025-09-05 23:47:37', 'choppe', 'student', 'active', NULL, 'Chandler', 'Adriel', 'Hoppe', 'other', '2005-06-27', 'Qatar', '4199083055', NULL, '+1-520-946-6576', NULL, '8862 Angeline Club Suite 967, Taliaville, ID 47654-5650', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Elyse Walker', NULL, '724-380-9047', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (102, 'Antwan Judge Hodkiewicz', 'rosa66@example.org', '2025-09-05 23:47:38', '$2y$12$5nFvqzy72GNOpBrxB533x.bfZ0nXE/1mY0Vm5GW/6aYHJJNMIWOhG', NULL, '2025-09-05 23:47:38', '2025-09-05 23:47:38', 'ahodkiewicz', 'student', 'inactive', NULL, 'Antwan', 'Judge', 'Hodkiewicz', 'other', '1998-12-08', 'El Salvador', '3449577507', NULL, '+1-231-923-2066', NULL, '3781 Medhurst Orchard Apt. 910, Swaniawskiview, NY 37190-2860', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Amelia Breitenberg', NULL, '762-980-2567', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (103, 'Kianna  Bahringer', 'uweber@example.net', '2025-09-05 23:47:38', '$2y$12$SDiyKnvDFoDx4Lz6sT2vTuURJzo6ZtUoM9sOyHiHxShYG08BKn4xe', NULL, '2025-09-05 23:47:38', '2025-09-05 23:47:38', 'kbahringer', 'student', 'active', NULL, 'Kianna', NULL, 'Bahringer', 'other', '2006-04-07', 'Luxembourg', '5447450729', NULL, '+1-830-957-1363', NULL, '1841 Orn Mountain, East Antoniaview, ND 88921-8619', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Samara Kovacek', NULL, '(954) 595-0339', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (105, 'Mittie  Mayer', 'gordon.bayer@example.net', '2025-09-05 23:47:38', '$2y$12$4y4qjjHy4CtVTd2g31Cj.OrY9qMEBjWjkME9.mIOZkLi5VXuFSIh2', NULL, '2025-09-05 23:47:38', '2025-09-05 23:47:38', 'mmayer', 'student', 'suspended', NULL, 'Mittie', NULL, 'Mayer', 'other', '2000-04-18', 'Mayotte', '0391603232', NULL, '(661) 217-0561', NULL, '329 Elisha Springs, Fredrickburgh, CT 71262', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Myrtis Schaden', NULL, '1-404-450-0926', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (106, 'Robyn Colt Beatty', 'ipouros@example.com', '2025-09-05 23:47:38', '$2y$12$QSiv24CZCZX1B1qYUdGnReAuoeDJuzJjjzWgD4L9yc1f.sqJwl9fG', NULL, '2025-09-05 23:47:38', '2025-09-05 23:47:38', 'rbeatty', 'student', 'active', NULL, 'Robyn', 'Colt', 'Beatty', 'other', '2003-11-09', 'Fiji', '6812791565', NULL, '(623) 518-8474', NULL, '6360 Karley Village, Hermannfurt, RI 82946', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Prof. Giles Hill Jr.', NULL, '1-480-514-3991', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (107, 'Hilario Mekhi Schuppe', 'dorothea.sporer@example.org', '2025-09-05 23:47:39', '$2y$12$U8TgF67OPq9jKzqb4k3mruMiEyHs9UssUFlofRpYcbFHCDofIO1r2', NULL, '2025-09-05 23:47:39', '2025-09-05 23:47:39', 'hschuppe', 'student', 'suspended', NULL, 'Hilario', 'Mekhi', 'Schuppe', 'other', '1997-06-25', 'Iceland', '8920650456', NULL, '+16236960741', NULL, '35241 Fritsch Hollow Suite 439, West Leopold, PA 48747-7963', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Geovany Balistreri DDS', NULL, '(283) 621-4026', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (113, 'Amely Otho Zulauf', 'otho.wunsch@example.com', '2025-09-05 23:47:40', '$2y$12$sqeoLc/TDu2WPQtMlAn8JuGJl.e/fvm5Lx81iE86jOOs7bgiLdV8S', NULL, '2025-09-05 23:47:40', '2025-09-05 23:47:40', 'azulauf', 'student', 'active', NULL, 'Amely', 'Otho', 'Zulauf', 'other', '2002-01-03', 'Malawi', '2210307558', NULL, '+1-870-301-7510', NULL, '3535 Hansen Island Suite 394, Moshemouth, WV 75995', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Santos Weissnat', NULL, '+1-443-538-0024', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (115, 'Kory  Murray', 'randi58@example.org', '2025-09-05 23:47:41', '$2y$12$VhyGUtqDLaqVmM.7N1Gz2ecMjT0.ucgpN84s3G3RtUTVp0TtOeKFC', NULL, '2025-09-05 23:47:41', '2025-09-05 23:47:41', 'kmurray', 'student', 'active', NULL, 'Kory', NULL, 'Murray', 'other', '1998-10-31', 'French Guiana', '3236106308', NULL, '260-702-2276', NULL, '9964 Pollich Camp Apt. 263, Miloside, ME 82376-6762', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Loren Breitenberg', NULL, '310.633.5433', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (121, 'Jeramie Joannie Funk', 'albin.huels@example.org', '2025-09-05 23:47:43', '$2y$12$x1g0ypzrDi9xNKUGVKaKyuyDj4mQZMyVO3G2v/MgPn8OHafOtGcSi', NULL, '2025-09-05 23:47:43', '2025-09-05 23:47:43', 'jfunk', 'student', 'inactive', NULL, 'Jeramie', 'Joannie', 'Funk', 'other', '1999-05-02', 'Afghanistan', '0016893612', NULL, '+16086693283', NULL, '77671 Roberta Port, South Paulland, AK 95908-9840', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Devin Treutel', NULL, '979.272.4662', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (122, 'Dejah  Schimmel', 'damian79@example.net', '2025-09-05 23:47:43', '$2y$12$TTrwtZY1Yq811x4G9RGrOud3zW2qhBTjcDlbuNCX6uHDEecN1mxKu', NULL, '2025-09-05 23:47:43', '2025-09-05 23:47:43', 'dschimmel', 'student', 'suspended', NULL, 'Dejah', NULL, 'Schimmel', 'other', '2006-02-24', 'Fiji', '1886319842', NULL, '1-352-878-2906', NULL, '87911 Zion Forks, Port Salvador, AR 16658', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Arielle Paucek', NULL, '+1 (712) 744-5105', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (127, 'Loyce Janice Pollich', 'gemard@example.net', '2025-09-05 23:47:44', '$2y$12$ZMPSWkUzbN2AVdWhPfy7Q.FDl6Zh19f0LAwDDYm491I2qELO/HV4K', NULL, '2025-09-05 23:47:44', '2025-09-05 23:47:44', 'lpollich1', 'student', 'active', NULL, 'Loyce', 'Janice', 'Pollich', 'other', '1996-08-03', 'Mexico', '0549070466', NULL, '1-563-872-5732', NULL, '65680 Jast Canyon Apt. 274, North Hermann, NE 98098-2177', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kacie Schuppe', NULL, '+1-667-652-5345', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (131, 'Alyson Westley Jacobs', 'zweber@example.com', '2025-09-05 23:47:45', '$2y$12$HksL6WcyWCUQUvR/iD4dOOO6m8HItn3tcU3gn18ug6hWlbUnO88AK', NULL, '2025-09-05 23:47:45', '2025-09-05 23:47:45', 'ajacobs', 'student', 'inactive', NULL, 'Alyson', 'Westley', 'Jacobs', 'other', '2004-05-08', 'Bhutan', '2850993723', NULL, '507-919-6675', NULL, '113 Rex View, New Aydenton, MO 83780-2041', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Walter Gibson II', NULL, '+1.248.996.6146', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (132, 'Shaniya Jeffrey Spinka', 'jonathan08@example.com', '2025-09-05 23:47:46', '$2y$12$emziupoqeta91j0gzxL41.zRssIBajO11S2.mc9TVwKx7KbJ2blMa', NULL, '2025-09-05 23:47:46', '2025-09-05 23:47:46', 'sspinka', 'student', 'suspended', NULL, 'Shaniya', 'Jeffrey', 'Spinka', 'other', '1996-02-02', 'Pakistan', '7723118620', NULL, '303.227.6813', NULL, '438 Lorenzo Highway Suite 221, Halvorsonchester, AL 16519', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pansy Rogahn', NULL, '585-766-2550', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (135, 'Brenda Stuart Wunsch', 'rau.marquise@example.com', '2025-09-05 23:47:46', '$2y$12$wIwbRgL1wxY8yHdIaQfSgeUYxBq1.4kZmMuW8zz.zjKHJ85/zxUiy', NULL, '2025-09-05 23:47:46', '2025-09-05 23:47:46', 'bwunsch', 'student', 'active', NULL, 'Brenda', 'Stuart', 'Wunsch', 'other', '2005-09-14', 'Montenegro', '0608195530', NULL, '+1 (959) 301-7772', NULL, '653 Dickinson Center, Ebbaland, IL 71989-6104', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dr. Raegan Beahan III', NULL, '+1-269-692-4446', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (137, 'Coby Faye Schuppe', 'feest.miles@example.net', '2025-09-05 23:47:47', '$2y$12$b5hugnIT7Rvt1ijfsHX6DOEDSJ70CWK0indrt/N5LyojflYDocdom', NULL, '2025-09-05 23:47:47', '2025-09-05 23:47:47', 'cschuppe', 'student', 'active', NULL, 'Coby', 'Faye', 'Schuppe', 'other', '1998-01-23', 'Mongolia', '0959365818', NULL, '+1-908-301-7203', NULL, '173 Shaylee Mill Apt. 975, Terrystad, GA 18948-2099', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Tony Connelly', NULL, '307.339.2595', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (140, 'Marianna Cali Schultz', 'king.berge@example.net', '2025-09-05 23:47:48', '$2y$12$NrDLDkfVbW5.wqo7ZeachOX09I/oKtrFK41ByrkGlSTu6wEu7jUNm', NULL, '2025-09-05 23:47:48', '2025-09-05 23:47:48', 'mschultz', 'student', 'active', NULL, 'Marianna', 'Cali', 'Schultz', 'other', '1995-12-25', 'New Zealand', '5121392834', NULL, '712-519-5732', NULL, '8479 McClure Mountain, Ziemannmouth, NH 47151', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Claudine Donnelly MD', NULL, '+1-773-897-4615', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (141, 'Elenor Susanna Klocko', 'qjerde@example.com', '2025-09-05 23:47:48', '$2y$12$V4B81chly5FYHwWDPrHX/.ZxHisUof5SJqmiBvfLcmRrd2MyFj6hW', NULL, '2025-09-05 23:47:48', '2025-09-05 23:47:48', 'eklocko', 'student', 'suspended', NULL, 'Elenor', 'Susanna', 'Klocko', 'other', '2002-05-06', 'Mongolia', '4033836809', NULL, '(801) 238-5485', NULL, '891 Geoffrey Springs Apt. 754, Rutherfordbury, NE 86959-0511', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Abagail Greenholt', NULL, '+1-283-609-4470', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (142, 'Madaline Gladyce Pfannerstill', 'corine79@example.net', '2025-09-05 23:47:48', '$2y$12$YZLFi3a93vHZi3kZU9Tfy.PprSuSGN4IjB2B62/sH7j4Zl/WQfeJ.', NULL, '2025-09-05 23:47:48', '2025-09-05 23:47:48', 'mpfannerstill', 'student', 'active', NULL, 'Madaline', 'Gladyce', 'Pfannerstill', 'other', '2005-02-14', 'Cyprus', '6484672216', NULL, '+1.919.937.6497', NULL, '90434 Nader Views, Goldenview, NJ 39283', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Corbin O''Reilly', NULL, '484.347.9284', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (143, 'Janis Eugene Windler', 'gardner05@example.org', '2025-09-05 23:47:49', '$2y$12$1/jJ3hinHuG/eLkOdufLEuanpsrAO/pEqiXFJxLQB07Y06JJdzSIC', NULL, '2025-09-05 23:47:49', '2025-09-05 23:47:49', 'jwindler', 'student', 'active', NULL, 'Janis', 'Eugene', 'Windler', 'other', '2000-05-25', 'Benin', '2769371346', NULL, '(458) 855-1864', NULL, '81442 Santino Crest, Mellieville, ID 61402-0464', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Belle Rutherford', NULL, '(616) 585-7898', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (145, 'Delta  Ankunding', 'norberto09@example.net', '2025-09-05 23:47:49', '$2y$12$e57.YnNcKS.GiqULw/VKruOOGP6Zm./aUCJJA0CJ5pzk/8M5dw7ee', NULL, '2025-09-05 23:47:49', '2025-09-05 23:47:49', 'dankunding', 'student', 'suspended', NULL, 'Delta', NULL, 'Ankunding', 'other', '2001-01-22', 'Kyrgyz Republic', '9942954620', NULL, '+1-408-609-8257', NULL, '6733 Murray Flat Apt. 027, Rainaville, ME 99344-9562', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Rafaela Smitham', NULL, '843.645.4568', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (147, 'Casimir  Ondricka', 'ularson@example.com', '2025-09-05 23:47:50', '$2y$12$UfCE2KvJrZHqAeo.PcITmOGNGvia.tyl02VtsDdRPia8OD7j1zbW.', NULL, '2025-09-05 23:47:50', '2025-09-05 23:47:50', 'condricka', 'student', 'active', NULL, 'Casimir', NULL, 'Ondricka', 'other', '1996-11-28', 'Guernsey', '2530485892', NULL, '+13852548488', NULL, '682 Johnson Prairie, Port Steveport, UT 25741', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Laney Reichel', NULL, '+1-586-636-0062', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (152, 'Chloe Toy Quigley', 'brennan94@example.org', '2025-09-05 23:47:51', '$2y$12$ECbm/lr.W7wD8ndyAAaHlOOZUGPORII187siXypo/LsM/DUGZfsRa', NULL, '2025-09-05 23:47:51', '2025-09-05 23:47:51', 'cquigley', 'student', 'active', NULL, 'Chloe', 'Toy', 'Quigley', 'other', '2006-10-01', 'Iraq', '4006802701', NULL, '+1 (352) 569-1727', NULL, '4992 Cordelia Creek Apt. 144, East Esperanzabury, AL 89319', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pete Ledner', NULL, '+16804010304', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, NULL, true, 0, NULL, NULL, false, NULL, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);
INSERT INTO public.users VALUES (146, 'Mozell Reggie Gislason', 'stanton.ford@example.org', '2025-09-05 23:47:49', '$2y$12$YPjZo7LXdLGEMVRKr545Nu9EU8iEb83zyfTAo/RkXClCfpKzm0ldi', NULL, '2025-09-05 23:47:49', '2025-09-10 12:35:21', 'mgislason', 'student', 'active', NULL, 'Mozell', 'Reggie', 'Gislason', 'other', '2004-08-01', 'Falkland Islands (Malvinas)', '0167238320', NULL, '747.671.0424', NULL, '83563 Gerhard Pine, Streichview, MO 36131', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Bonita Swift Sr.', NULL, '1-217-620-5785', NULL, NULL, NULL, '{}', 'UTC', 'en', '{}', NULL, NULL, NULL, '2025-09-10 12:35:21', false, 0, NULL, NULL, false, NULL, NULL, 48, '{}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', false, true);


--
-- Data for Name: academic_plans; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: academic_terms; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.academic_terms VALUES (2, '2026-SPRING', 'Spring 2026', 'spring', 2026, '2026-01-15', '2026-05-15', '2025-12-01', '2025-12-31', '2026-01-29', '2026-03-26', '2026-05-22', false, true, NULL, '2025-08-25 13:18:39', '2025-08-25 13:18:39', NULL, false, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.academic_terms VALUES (5, 'FA2024', 'Fall 2024', 'fall', 2024, '2024-08-15', '2024-12-15', '2024-07-01', '2024-08-10', '2024-08-29', '2024-11-15', '2024-12-22', false, true, '{"midterm_end": "2024-10-18", "midterm_start": "2024-10-14", "final_exam_end": "2024-12-14", "final_exam_start": "2024-12-09", "thanksgiving_break_end": "2024-11-29", "thanksgiving_break_start": "2024-11-25"}', '2025-09-03 16:59:39', '2025-09-03 16:59:39', '2024-09-01', false, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.academic_terms VALUES (1, '2025-FALL', 'Fall 2025', 'fall', 2025, '2025-09-01', '2025-12-15', '2025-08-01', '2025-08-31', '2025-09-15', '2025-11-10', '2025-12-22', false, true, NULL, '2025-08-25 13:18:39', '2025-08-25 13:18:39', NULL, true, '2025-11-14', '2025-08-14', NULL, '2025-12-14', 500);
INSERT INTO public.academic_terms VALUES (10, 'SPRING-2026', 'Spring 2026', 'spring', 2026, '2026-01-19', '2026-05-15', '2025-10-15', '2026-01-12', '2026-02-02', '2026-04-01', '2026-05-20', false, true, NULL, '2025-09-18 11:20:56', '2025-09-18 11:20:56', NULL, true, '2025-11-15', '2025-08-01', '2025-10-15', '2025-12-15', 400);
INSERT INTO public.academic_terms VALUES (11, 'SUMMER-2026', 'Summer 2026', 'summer', 2026, '2026-06-01', '2026-08-14', '2026-03-01', '2026-05-25', '2026-06-12', '2026-07-15', '2026-08-18', false, true, NULL, '2025-09-18 11:20:56', '2025-09-18 11:20:56', NULL, true, '2026-04-01', '2025-09-01', '2026-02-01', '2026-05-01', 200);
INSERT INTO public.academic_terms VALUES (3, '2025-SUMMER', 'Summer 2025', 'summer', 2025, '2026-06-01', '2026-08-15', '2026-05-01', '2026-05-31', '2026-06-15', '2026-08-10', '2026-08-22', true, true, NULL, '2025-08-25 13:18:39', '2025-08-25 13:18:39', NULL, false, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.academic_terms VALUES (9, 'FALL-2025', 'Fall 2025', 'fall', 2025, '2025-09-02', '2025-12-19', '2025-05-01', '2025-08-25', '2025-09-16', '2025-11-15', '2025-12-23', false, true, NULL, '2025-09-18 11:20:56', '2025-09-18 11:20:56', NULL, true, '2025-12-01', '2025-01-01', '2024-12-15', '2025-04-15', 500);
INSERT INTO public.academic_terms VALUES (15, 'FALL2025', 'Fall 2025', 'fall', 2025, '2025-09-01', '2025-12-20', '2025-08-15', '2025-09-05', '2025-09-15', '2025-11-01', '2025-12-31', true, true, NULL, '2025-09-25 17:27:24', '2025-09-25 17:27:24', NULL, true, '2025-08-01', '2025-01-01', '2025-03-01', '2025-08-15', NULL);


--
-- Data for Name: academic_standing_changes; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: applicants; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.applicants VALUES (1, 167, 'APP-2025-00001', 'John', NULL, 'Applicant', '2005-03-15', 'male', '555-0123', NULL, '123 Main Street', 'Springfield', 'IL', 'USA', '62701', 'USA', NULL, NULL, NULL, NULL, 'active', '2025-09-16 11:04:36', '2025-09-16 11:04:36', NULL);
INSERT INTO public.applicants VALUES (2, 168, 'APP-2025-00002', 'Jane', NULL, 'Test', '2004-08-22', 'female', '555-0456', NULL, '456 Oak Avenue', 'Chicago', 'IL', 'USA', '60601', 'USA', NULL, NULL, NULL, NULL, 'active', '2025-09-16 11:04:36', '2025-09-16 11:04:36', NULL);
INSERT INTO public.applicants VALUES (3, 169, 'APP-2025-00003', 'David', NULL, 'International', '2003-12-10', 'male', '+44-20-7123-4567', NULL, '10 Downing Street', 'London', '', 'UK', 'SW1A 2AA', 'UK', 'UK123456789', NULL, NULL, NULL, 'active', '2025-09-16 11:04:36', '2025-09-16 11:04:36', NULL);
INSERT INTO public.applicants VALUES (4, 170, 'APP-2025-00004', 'Sarah', NULL, 'Graduate', '1998-06-30', 'female', '555-0789', NULL, '789 University Blvd', 'Boston', 'MA', 'USA', '02134', 'USA', NULL, NULL, NULL, NULL, 'active', '2025-09-16 11:04:36', '2025-09-16 11:04:36', NULL);


--
-- Data for Name: admission_applications; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.admission_applications VALUES (31, 'APP-2025-000009', 'a4e2c207-401f-42d9-9e14-8b23aa55c255', 'Axel', 'Irwin', 'Spencer', NULL, '2007-03-10', 'male', 'Central African Republic', NULL, NULL, '539637190956', 'nora67@example.org', '586-659-3188', '854.422.4014', '26825 Yost Motorway', '192 Lebsack Ports', 'Smithambury', 'Georgia', '89846', 'Cameroon', 'Elian Zboncak', 'Parent', '(234) 787-7505', 'rylan.botsford@example.org', NULL, NULL, NULL, NULL, NULL, 'graduate', 1, 2, NULL, NULL, NULL, 'fall', 2025, 'DuBuque Ltd University', 'Poland', NULL, NULL, NULL, 3.05, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"GRE":{"verbal":145,"quantitative":142,"analytical":5.7,"test_date":"2025-06-04"}}', 'Dolor aliquam enim sit libero reprehenderit ex id. Aperiam est aut beatae nesciunt sunt adipisci. Eum minima dolorum accusantium sit et.

Architecto eos id id enim veritatis voluptate iste. Nihil quas impedit est eum. Corporis voluptatem qui doloremque eaque fuga. Qui qui atque expedita sapiente est non.

Voluptatibus iusto est mollitia laborum ut quod nemo. Impedit ea minus excepturi cupiditate placeat. Qui est ut ut voluptates rem cupiditate.', 'Qui explicabo autem impedit. Magni laboriosam cum impedit doloribus. Voluptate beatae praesentium maiores quis est accusamus. Quia ea omnis aut et.

Dolore minima quis ex nihil. Qui ut autem voluptatem. Sed quis voluptate expedita eum dolor quaerat id iure.

Possimus qui eligendi expedita et et. Sed modi similique reiciendis facere voluptatem omnis sint. A iusto dolore dignissimos eveniet ratione odit voluptas.

Qui quibusdam temporibus vel eum. Ut amet id qui. Dolor rerum dolorum omnis incidunt voluptates deserunt.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'under_review', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-08-07', NULL, false, false, NULL, '2025-07-07 13:55:42', '2025-08-27 09:34:06', NULL, '2025-09-17 15:38:45', '2025-12-11 15:49:17', 184, NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-17 15:38:45', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (44, 'APP-2025-000022', '24b377b9-ebcd-4e2b-96bb-d7d77b8e3e3b', 'Rosario', NULL, 'Lowe', NULL, '2002-12-13', 'other', 'Svalbard & Jan Mayen Islands', NULL, NULL, '138729938898', 'dlueilwitz@example.net', '+1-479-443-6412', '605-204-8562', '8920 Williamson Plains Apt. 002', '6324 Corwin Islands', 'Wildermantown', 'California', '88330', 'Trinidad and Tobago', 'Shyann DuBuque', 'Sibling', '+1-260-524-1388', 'rtremblay@example.org', NULL, NULL, NULL, NULL, NULL, 'freshman', 1, 2, NULL, NULL, NULL, 'fall', 2025, 'Hermiston-Walter University', 'Hungary', NULL, NULL, NULL, 2.73, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '[]', 'Commodi laboriosam sit occaecati nihil sequi. Delectus veritatis quasi earum fugiat quo unde. Ut commodi recusandae voluptatem nulla.

Dolor voluptatem minima qui aut recusandae qui. Autem quia voluptatem ab deleniti. Id iste quasi eligendi modi. Omnis corrupti accusantium eligendi animi voluptatum dolores.

Mollitia adipisci ullam et nostrum consequatur. Consequuntur qui excepturi vel enim reprehenderit.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'denied', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-09-06', NULL, false, false, NULL, '2025-08-19 16:54:49', '2025-09-11 12:24:53', NULL, '2025-09-17 15:38:47', '2025-12-11 15:49:18', 190, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:47', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (37, 'APP-2025-000015', '7e7c0e62-c386-476b-8aef-73755e0f43e7', 'Arden', 'Kobe', 'Kessler', NULL, '2007-10-01', 'female', 'Cote d''Ivoire', NULL, NULL, '620562463157', 'shane.zieme@example.net', '+1-606-671-1750', NULL, '839 Conn Plain', '71641 Parisian Neck', 'East Jadyn', 'Hawaii', '01670-9610', 'Tokelau', 'Abigail Reynolds DDS', 'Sibling', '212.801.0065', 'keeling.emory@example.net', NULL, NULL, NULL, NULL, NULL, 'graduate', 1, 2, NULL, NULL, NULL, 'fall', 2025, 'Farrell-Jacobi University', 'Honduras', NULL, NULL, NULL, 3.24, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"GRE":{"verbal":145,"quantitative":149,"analytical":3.2,"test_date":"2023-10-01"}}', 'Voluptas quia aliquam expedita blanditiis. Ea facere qui aliquam odit magni. Voluptatum in dolorum culpa eaque possimus voluptatem rerum. In fugiat et deserunt ratione illo quis cupiditate.

Animi voluptatem omnis omnis aut quis laboriosam id. Fugit perferendis et ut officia. Facilis dignissimos est saepe. Molestiae suscipit explicabo minima recusandae consequatur sint.

Rem deserunt laboriosam laudantium ut veritatis rerum omnis. Qui optio esse rerum et corrupti. Veniam atque adipisci adipisci consequuntur.', 'Magni aliquam ut nemo veritatis laborum. Porro fuga exercitationem quo suscipit.

Iste corrupti et ad nostrum autem. Esse non aut non odio. Et ipsam aperiam dolores et occaecati harum. Aut iusto nostrum tenetur nihil corrupti et.

Illo repudiandae quis voluptas voluptate error. Ut odit unde neque id ut nulla error. Vero consequatur et velit qui expedita. Amet adipisci debitis explicabo sit corrupti laudantium voluptas quis.

Corrupti et ad maiores temporibus fuga sint. Quod tenetur nihil dignissimos et. Molestias eius animi quas tempore totam est sunt. Quasi quaerat cupiditate consequatur sit rerum.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'interview_scheduled', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-07-14', NULL, false, false, NULL, '2025-07-25 18:49:16', '2025-08-14 16:54:04', NULL, '2025-09-17 15:38:47', '2025-12-11 15:49:18', 191, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:47', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (39, 'APP-2025-000017', 'f22daa78-b5b4-42ad-afbe-22f277da1dc9', 'Luciano', 'Edyth', 'Schneider', NULL, '2006-01-24', 'male', 'Saint Lucia', NULL, NULL, '344879732846', 'shannon19@example.net', '615-696-9572', '+1.534.518.6595', '449 Luna Greens', '4331 Klein Shoals Suite 281', 'Port Rashawn', 'Nevada', '91358', 'Micronesia', 'Prof. Verona Wiegand MD', 'Parent', '559-460-9816', 'owintheiser@example.net', NULL, NULL, NULL, NULL, NULL, 'freshman', 1, 5, NULL, NULL, NULL, 'fall', 2025, 'Turner Ltd University', 'Iceland', NULL, NULL, NULL, 3.43, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"SAT":{"total":1062,"math":464,"verbal":435,"test_date":"2024-09-21"}}', 'Repudiandae voluptatum dolorem minus ipsum. Consectetur omnis assumenda ducimus nisi sunt. Quia velit non quaerat aut ut accusantium sint qui. Incidunt voluptatum recusandae nisi vero.

Aut repellendus similique deleniti similique quia. Dolor enim qui voluptas. Vitae enim voluptas nobis consequatur.

Nam consectetur voluptates et aut id maxime iusto. A omnis ipsum sequi iusto molestiae. Et et laborum at non laudantium. Ratione omnis vitae maxime architecto debitis suscipit illo.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'decision_pending', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-08-07', NULL, false, false, NULL, '2025-06-15 22:45:54', '2025-09-03 09:23:58', NULL, '2025-09-17 15:38:47', '2025-12-11 15:49:18', 193, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:47', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (45, 'APP-2025-71646', '2f182625-4698-4338-839c-c7bbdf88b9d3', 'John', NULL, 'Applicant', NULL, '2005-03-15', 'male', 'American', NULL, NULL, NULL, 'applicant@example.com', '555-0123', NULL, '123 Main Street, Springfield, IL 62701', '123 Main Street, Springfield, IL 62701', 'Springfield', 'IL', '62701', 'USA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'freshman', 2, 1, NULL, NULL, NULL, NULL, 2025, NULL, NULL, NULL, NULL, NULL, 3.80, '4.0', NULL, NULL, 'Springfield High School', 'USA', '2023-05-30', NULL, NULL, 'I am passionate about computer science and technology...', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, false, NULL, NULL, NULL, false, false, NULL, '2025-09-16 11:49:15', NULL, NULL, NULL, NULL, 167, NULL, NULL, NULL, '2025-09-16 11:49:15', '2025-09-16 11:49:15', NULL, 1, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (56, 'APP-2025-000056', '8f7e6d5c-4b3a-2e1d-9f8e-7c6b5a4d3e2f', 'Kwame', 'Asante', 'Mensah', NULL, '2005-03-15', 'male', 'Ghanaian', 'Ghana', NULL, NULL, 'kwame.mensah@example.com', '+233244123456', NULL, '123 Independence Avenue, Accra', '123 Independence Avenue, Accra', 'Accra', 'Greater Accra', NULL, 'Ghana', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'freshman', 15, 13, NULL, NULL, NULL, 'spring', 2025, 'Achimota School', 'Liberia', NULL, NULL, NULL, 3.85, '4.0', 89, 98, 'Achimota School', 'Ghana', '2024-06-30', 'regular', '{"WASSCE":{"english":"B3","mathematics":"A1","science":"C4","social":"B2","year":"2024","additional":"Physics: A1, Chemistry: B2, ICT: A1, Economics: B3"}}', 'I am passionate about technology and its potential to transform Africa...', NULL, NULL, NULL, NULL, '[{"name":"Test Activity","position":"Leader","years":"","hours":0,"description":""}]', '[]', NULL, NULL, '[{"name":"Roosevelt C. Gibson","title":"","email":"diggygibson.rg@gmail.com","relationship":"","institution":"","phone":"","status":"pending"},{"name":"Maya Nyla","title":"","email":"mala@gibson.com","relationship":"","institution":"","phone":"","status":"pending"}]', 'submitted', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, false, 50.00, NULL, NULL, false, false, NULL, '2025-09-20 17:27:24', '2025-09-22 17:27:24', NULL, '2025-09-25 17:27:24', '2025-12-23 15:02:02', NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '[{"timestamp":"2025-09-24T15:02:02+00:00","action":"application_started","ip":"172.18.0.1"},{"timestamp":"2025-09-25T11:42:44+00:00","action":"status_change","from":"draft","to":"submitted","user_id":null}]', '2025-09-24 15:02:02', '2025-09-25 17:27:24', NULL, NULL, NULL, NULL, '{"recommendations":{"recommender_1_name":"Roosevelt C. Gibson","recommender_1_title":null,"recommender_1_email":"diggygibson.rg@gmail.com","recommender_1_relationship":"mentor","recommender_2_name":"Maya Yasmin","recommender_2_title":null,"recommender_2_email":"myasmin@gmail.com","recommender_2_relationship":"employer","additional_recommender_name":null,"additional_recommender_email":null}}');
INSERT INTO public.admission_applications VALUES (24, 'APP-2025-000002', '7371a801-9b08-4cdf-a7c3-0bed0f800111', 'Kory', NULL, 'Mueller', NULL, '2004-12-29', 'male', 'Paraguay', NULL, NULL, '676272718835', 'bosco.mohamed@example.net', '(737) 219-4294', NULL, '15492 Stiedemann Branch', '650 Hackett Trail', 'North Rosarioburgh', 'West Virginia', '36353', 'Finland', 'Otilia Kulas', 'Sibling', '(484) 686-3317', 'sipes.brenden@example.com', NULL, NULL, NULL, NULL, NULL, 'transfer', 1, 5, NULL, NULL, NULL, 'fall', 2025, 'Cassin, Goldner and Schaefer University', 'Liberia', NULL, NULL, NULL, 3.12, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '[]', 'Laudantium voluptatum alias atque placeat. Necessitatibus nostrum consequatur doloremque unde atque cum. Voluptatem voluptatem qui ut non. Sed perferendis labore laudantium consequatur et.

Quis id deserunt commodi enim. Quos veritatis animi est pariatur consequatur doloremque. Ut nisi sit enim.

Iste expedita velit eum reiciendis magni. Et quia error soluta aut et magnam nam inventore. Perferendis corporis voluptas eos voluptas velit velit. Voluptatem corrupti itaque sunt ab ipsum rem exercitationem.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, false, 75.00, NULL, NULL, false, false, NULL, '2025-08-10 11:12:36', NULL, NULL, '2025-09-17 15:38:43', '2025-12-11 15:49:17', 178, NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-17 15:38:43', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (26, 'APP-2025-000004', '907c22af-15af-4623-a8f9-035c48919d31', 'Jacques', 'Oma', 'Reilly', NULL, '1997-06-17', 'other', 'Mozambique', NULL, NULL, '102040202257', 'bruen.amaya@example.org', '(385) 529-6747', NULL, '221 Winona Crest', '43329 Ernestina Villages Suite 033', 'Port Andre', 'Ohio', '27778-3853', 'Isle of Man', 'Kristian Rippin', 'Spouse', '562.909.9277', 'ystehr@example.com', NULL, NULL, NULL, NULL, NULL, 'graduate', 1, 5, NULL, NULL, NULL, 'fall', 2025, 'Boyle LLC University', 'Israel', NULL, NULL, NULL, 3.15, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"GRE":{"verbal":151,"quantitative":149,"analytical":4.8,"test_date":"2023-11-26"}}', 'Praesentium velit voluptatem qui corrupti natus tenetur. Ut perferendis et est tempore nihil officiis. Voluptates quia ex cum et. Quibusdam quia eaque at possimus ducimus perspiciatis.

Quia omnis explicabo deleniti. Autem repellat nostrum temporibus quo ducimus quaerat dolorem. Tempora quis dicta dolorem vel eum voluptatem ab.

Non et reprehenderit voluptatem. Alias veniam minus fuga aut dolorum ipsum asperiores sit. Optio non enim sunt sint hic expedita sint. Maxime alias quidem saepe.', 'Et pariatur deserunt voluptatem eius voluptas et at. Similique aut quidem aut voluptatem commodi consequatur. Molestias a sed voluptatum officiis quaerat fuga. Ut quibusdam repellendus veritatis est dolorem dolores. Quos a eaque quaerat consequatur fugit aut.

Autem aspernatur rerum aut sunt quos. Distinctio vero neque distinctio. Recusandae omnis repudiandae est porro asperiores error vitae.

Corporis voluptas quaerat praesentium non harum. Ipsum doloribus deserunt autem et. Perferendis quae ab harum earum. Aspernatur facilis odio excepturi voluptas.

Dolores nulla placeat repellat voluptates. Corporis dolore quidem sint qui rerum. Dolorum et perspiciatis quas. Ad impedit qui ducimus asperiores.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'submitted', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-08-11', NULL, false, false, NULL, '2025-06-18 18:17:26', '2025-09-02 13:00:47', NULL, '2025-09-17 15:38:44', '2025-12-11 15:49:17', 180, NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-17 15:38:44', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (32, 'APP-2025-000010', 'c61b3e74-754e-491c-8750-5c8a0f7f02a5', 'Helen', 'Reed', 'Abshire', NULL, '2007-05-04', 'male', 'Kyrgyz Republic', NULL, NULL, '163079992028', 'thea76@example.net', '+1 (657) 893-9836', '(727) 980-0244', '377 Lucile Causeway Apt. 955', '12452 Walter Place Apt. 543', 'Pfannerstillmouth', 'New Jersey', '13157', 'Kuwait', 'Prof. Joy Kovacek IV', 'Sibling', '747-836-3635', 'aubree30@example.org', NULL, NULL, NULL, NULL, NULL, 'freshman', 1, 2, NULL, NULL, NULL, 'fall', 2025, 'Prosacco Ltd University', 'Congo', NULL, NULL, NULL, 2.60, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '[]', 'Cum amet ipsam et sunt eum. Quae consequuntur est porro architecto libero. Aut maiores dolore rerum. Ut cupiditate sit et laboriosam sunt omnis.

Omnis incidunt culpa consequuntur quia quo nam. Voluptatum nam alias voluptates dicta et. Doloribus tempore praesentium quo ullam sunt. Et et nulla eius ut ipsa aut.

Saepe laudantium occaecati et expedita error. Ipsa quis dolorem provident commodi provident tempore nihil. Voluptatem sunt est aut neque adipisci rem. Ut rerum voluptate autem veritatis harum.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'under_review', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-07-26', NULL, false, false, NULL, '2025-07-06 05:44:24', '2025-08-28 01:04:42', NULL, '2025-09-17 15:38:45', '2025-12-11 15:49:17', 185, NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-17 15:38:45', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (28, 'APP-2025-000006', '8928375d-9dfa-4c31-af8e-60aed5a23cb5', 'Maximo', 'Tommie', 'Hauck', NULL, '2008-02-09', 'other', 'Malta', NULL, NULL, '365249287919', 'bruen.orland@example.net', '937.721.6334', '479.833.7128', '70772 Robel Brook', '406 Aletha Circle', 'New Erinshire', 'Virginia', '75100', 'Swaziland', 'Nelson Stamm DDS', 'Friend', '1-956-594-5361', 'santina.johnston@example.com', NULL, NULL, NULL, NULL, NULL, 'graduate', 1, 2, NULL, NULL, NULL, 'fall', 2025, 'Casper-Hills University', 'Togo', NULL, NULL, NULL, 2.33, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"GRE":{"verbal":146,"quantitative":136,"analytical":4.3,"test_date":"2024-06-18"}}', 'Explicabo sint quia sapiente. Ipsam quis quo vel pariatur et impedit. Occaecati dolores debitis doloribus voluptatem.

Mollitia alias alias eligendi similique ut ipsam quae officia. Sit repellat neque quia autem recusandae. Pariatur molestiae autem fuga odio velit qui. Quia neque vel doloribus minus. Est quia et ipsa fuga et voluptate.

Nihil unde autem placeat ab. Esse aut deleniti esse et error. Nihil sed aspernatur iusto commodi laboriosam provident.', 'Molestias blanditiis eveniet molestiae sed id. Expedita saepe aliquam expedita rem doloribus id. Non ea aut nemo error dicta labore. Aut cumque qui voluptatibus.

Et est nulla quo necessitatibus nam odit fuga. Eius minima est ipsum tempora. Ipsa alias est suscipit quisquam placeat aspernatur.

Aut porro saepe sequi non dolorum. Modi velit facilis ratione ipsa. Sapiente sapiente sed occaecati fugit aliquid vero velit quis. Ut aliquid nemo soluta dolorem et laboriosam. At et dignissimos minima alias dolorem.

Pariatur dolores sed autem quae culpa fugiat. Iste minima et aperiam molestiae. Consectetur voluptatem ut fuga nihil nihil corrupti necessitatibus eaque. Voluptate ea architecto sed.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'submitted', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-07-15', NULL, false, false, NULL, '2025-07-18 11:55:26', '2025-08-18 04:05:10', NULL, '2025-09-17 15:38:45', '2025-12-11 15:49:17', 182, NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-17 15:38:45', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (29, 'APP-2025-000007', 'dbff6f99-db3c-426f-a51b-664176bc425f', 'Vivienne', NULL, 'Rippin', NULL, '2008-03-30', 'male', 'Ireland', NULL, NULL, '288971757600', 'ola.harris@example.net', '+1.435.394.3515', NULL, '875 Swaniawski Heights Apt. 135', '6041 Cartwright Keys', 'Mullerview', 'Washington', '15163-1019', 'Guam', 'Cassandre Gibson', 'Parent', '(267) 927-9936', 'qjohnston@example.com', NULL, NULL, NULL, NULL, NULL, 'transfer', 1, 5, NULL, NULL, NULL, 'fall', 2025, 'Pouros-Schultz University', 'Djibouti', NULL, NULL, NULL, 2.14, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '[]', 'Nulla dolor facere id soluta numquam et. Sint odio eveniet natus sed dolorem omnis excepturi. Dolore illum dolor reiciendis error. Praesentium consequuntur nostrum a odit numquam.

Qui earum possimus ipsa maiores consequatur suscipit est ducimus. Sunt reprehenderit consectetur fugiat explicabo. Atque est sint reprehenderit qui omnis dicta aliquid.

Quidem possimus voluptatum sunt cum voluptates pariatur. Provident qui est dolorum aut sed neque. Repudiandae magni quaerat necessitatibus saepe voluptatem. Quia ad mollitia quo libero exercitationem sint odit.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'submitted', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-09-07', NULL, false, false, NULL, '2025-07-28 10:18:17', '2025-09-04 12:38:31', NULL, '2025-09-17 15:38:45', '2025-12-11 15:49:17', 183, NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-17 15:38:45', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (33, 'APP-2025-000011', '2985a142-0478-4055-9490-189cae6a0ecc', 'Hadley', 'Daphnee', 'Zulauf', NULL, '2007-11-14', 'other', 'Canada', NULL, NULL, '688898876589', 'jovan.little@example.net', '1-541-243-7613', NULL, '30748 Turner Trafficway Suite 741', '680 Velva Run', 'Ronnyfort', 'Massachusetts', '25314', 'Venezuela', 'Giovanny Herzog', 'Spouse', '1-831-956-5568', 'harvey19@example.net', NULL, NULL, NULL, NULL, NULL, 'transfer', 1, 1, NULL, NULL, NULL, 'fall', 2025, 'Bergnaum, Rau and Wuckert University', 'Saint Vincent and the Grenadines', NULL, NULL, NULL, 3.03, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '[]', 'Sapiente et eum sit ut recusandae. Blanditiis aut est sunt explicabo ea distinctio ut. Quasi aut doloremque unde aut qui eum vitae. Repellendus et quos aspernatur. Aliquid asperiores minus dicta aut qui occaecati.

Qui suscipit nobis id sint modi voluptatem. Accusamus temporibus quidem dolor possimus. Voluptatem expedita est ipsa dolorum veniam voluptate.

Earum et aut earum. Voluptatem ut quia sit quia ut ut. Officia est repudiandae sit. Quisquam molestiae blanditiis natus molestiae.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'documents_pending', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-07-15', NULL, false, false, NULL, '2025-06-23 06:46:30', '2025-09-12 01:10:36', NULL, '2025-09-17 15:38:46', '2025-12-11 15:49:18', 186, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:46', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (34, 'APP-2025-000012', 'd8982676-1ab5-46b8-b459-1c87330e680f', 'Lura', NULL, 'Bashirian', NULL, '2002-04-25', 'female', 'Swaziland', NULL, NULL, '345985612268', 'feil.sigrid@example.org', '740-787-3333', NULL, '49398 Hans Park', '3585 Gerhard Court Suite 758', 'Rippinbury', 'Wyoming', '08416', 'Somalia', 'Chadrick Rohan', 'Sibling', '843.216.5518', 'vmetz@example.net', NULL, NULL, NULL, NULL, NULL, 'transfer', 1, 1, NULL, NULL, NULL, 'fall', 2025, 'Ward-Bauch University', 'Djibouti', NULL, NULL, NULL, 3.33, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '[]', 'Repellat unde nam dolorem consequatur eos hic sit. Sit rem est id est qui ut itaque repudiandae.

Illo qui non non eveniet. Eos facere facilis itaque odio debitis qui. Ipsum quasi quae dolor sint inventore non quos. Itaque saepe pariatur eligendi blanditiis porro voluptas ea.

Numquam aliquam expedita ipsam sunt. Veritatis sunt reiciendis ut ea voluptas. Odio exercitationem ea quia consequatur temporibus reiciendis eum.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'documents_pending', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-07-13', NULL, false, false, NULL, '2025-08-10 03:48:42', '2025-08-31 04:17:16', NULL, '2025-09-17 15:38:46', '2025-12-11 15:49:18', 187, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:46', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (35, 'APP-2025-000013', '77f63277-ce52-4392-b01a-36f1b3a72d56', 'Colt', NULL, 'Bashirian', NULL, '1996-02-02', 'other', 'Mali', NULL, NULL, '846848388210', 'mgutmann@example.org', '564.850.0696', '+17195776240', '95189 Kub Stravenue Apt. 697', '432 Mayert Grove Apt. 923', 'Lolitabury', 'Nevada', '18028-1931', 'Namibia', 'Kayleigh Ryan', 'Sibling', '1-515-902-6337', 'mavis20@example.net', NULL, NULL, NULL, NULL, NULL, 'freshman', 1, 5, NULL, NULL, NULL, 'fall', 2025, 'Berge, Hilpert and Hartmann University', 'Singapore', NULL, NULL, NULL, 3.57, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"SAT":{"total":1553,"math":527,"verbal":449,"test_date":"2025-07-31"}}', 'Voluptas iusto ut natus. Maiores praesentium vero omnis quaerat.

Nam fuga quasi nisi officia sit dolor dolor. Sequi tenetur quibusdam ab aut dicta id. Iure eius quas qui.

Vel et nulla vitae voluptatem est molestiae. Odit quia eos ut harum ut at molestiae. Et ut quisquam molestiae. Non culpa et est quasi quod rerum rerum libero.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'committee_review', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-07-12', NULL, false, false, NULL, '2025-06-23 18:20:00', '2025-09-08 07:13:44', NULL, '2025-09-17 15:38:46', '2025-12-11 15:49:18', 188, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:46', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (36, 'APP-2025-000014', '192d4270-f986-409e-8e75-8fc849f153cf', 'Chet', 'Gregory', 'Considine', NULL, '1996-11-04', 'male', 'Malaysia', NULL, 'II747803', NULL, 'lockman.danika@example.net', '615-464-3528', NULL, '63689 George Cliff Suite 652', '8375 Marques Well Apt. 049', 'New Vita', 'New Hampshire', '05832', 'Tajikistan', 'Daisha Runolfsdottir DVM', 'Sibling', '765-995-8459', 'djenkins@example.com', NULL, NULL, NULL, NULL, NULL, 'international', 1, 1, NULL, NULL, NULL, 'fall', 2025, 'Reichert, Franecki and Reynolds University', 'Antigua and Barbuda', NULL, NULL, NULL, 3.50, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"TOEFL":{"total":92,"test_date":"2025-05-26"}}', 'Maiores ea architecto sapiente ipsum amet quos. Quae molestiae ut eum nihil ea. Eaque rerum non autem quo aut officiis enim. Sit impedit voluptate ut cupiditate quisquam voluptatibus.

Tenetur eaque sed dignissimos eaque velit. Est est doloribus dolores similique iure. Inventore maiores necessitatibus iste ipsum ea dignissimos rerum. Et maxime et eaque assumenda quae.

Ex odio vel dolores nemo est rem rerum ipsam. Minus autem ratione qui sunt recusandae sequi. Quis totam et beatae eos et doloribus voluptatum. Expedita aperiam delectus vel eius voluptate dignissimos.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'committee_review', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-07-22', NULL, false, false, NULL, '2025-07-06 10:34:22', '2025-08-25 00:20:13', NULL, '2025-09-17 15:38:46', '2025-12-11 15:49:18', 189, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:46', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (55, 'APP2025090001', 'eaee080b-31ca-461c-b6f9-493a31d9ca6d', 'Roosevelt', 'C.', 'Gibson', NULL, '1999-10-05', 'male', 'Liberian', NULL, NULL, NULL, 'diggygibson.rg@gmail.com', '+231886711477', NULL, 'Tubman Boulevard, Oldest Congo Town', 'Tubman Boulevard, Oldest Congo Town', 'Monrovia', 'Montserrado', NULL, 'Liberia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'freshman', 10, 1, NULL, NULL, NULL, 'spring', 2026, 'Harvest Senior Secondary Academy', 'Liberia', NULL, NULL, NULL, 3.25, '4.0', NULL, NULL, 'Harvest Senior Secondary Academy', 'Liberia', '2025-08-06', NULL, '"[]"', 'I have always believed that education is not only about acquiring knowledge but also about using that knowledge to create meaningful change. Growing up, I was exposed to both challenges and opportunities that shaped my outlook on life. These experiences motivated me to set ambitious goals for myselfΓÇögoals centered on making an impact within my community and beyond.

My aspiration is to pursue a career that blends innovation, service, and leadership. I am motivated by the idea that higher education is not simply a stepping stone to a career, but a platform to discover new perspectives, strengthen skills, and form connections that last a lifetime. At your institution, I see an environment that encourages intellectual curiosity, collaboration, and the pursuit of excellence.

What excites me most about your program is how it emphasizes not just academic achievement, but also personal growth and community engagement. I want to become the type of leader who is not only technically skilled but also compassionate, adaptable, and thoughtful in decision-making. By immersing myself in the opportunities availableΓÇöwhether through research, mentorship, or campus activitiesΓÇöI hope to sharpen my critical thinking and problem-solving skills while also developing the confidence to contribute meaningfully in diverse environments.

In the long run, I envision myself applying the knowledge and experiences gained here to address real-world issues, especially in areas where education and innovation can transform lives. Your program will provide the foundation I need to achieve this vision. It offers the right mix of rigorous academics, supportive faculty, and opportunities for hands-on learning that align perfectly with my objectives.

Ultimately, I am motivated by the belief that the education I receive will not end with me; rather, it will be a tool I can use to empower others and create pathways for progress. By pursuing higher education at your institution, I am confident that I will gain both the skills and the mindset necessary to make that vision a reality.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, false, 50.00, NULL, NULL, false, false, NULL, '2025-09-22 16:11:27', NULL, NULL, '2025-09-23 11:05:04', '2025-12-21 16:11:27', NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '"[{\"timestamp\":\"2025-09-22T16:11:27+00:00\",\"action\":\"application_started\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:51:13+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:51:17+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:51:28+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:51:32+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:51:43+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:51:46+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:51:57+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:52:00+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:52:11+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:52:15+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:52:27+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:52:31+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:52:41+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:52:45+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:53:00+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:53:04+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:53:14+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:53:18+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:53:29+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:53:33+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:53:42+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:53:49+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:53:53+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:54:05+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:54:08+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:54:20+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:54:23+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:54:33+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:54:36+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:55:54+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:55:57+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:56:00+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:56:11+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:56:14+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:56:25+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:56:29+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:56:39+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:56:43+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:56:53+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:56:56+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:57:08+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:57:16+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:57:24+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:57:32+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:57:39+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:57:53+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:58:00+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:58:07+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:58:14+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:58:21+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:58:28+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:58:37+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:58:50+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:58:54+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:59:07+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:59:12+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:59:26+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:59:30+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:59:44+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T21:59:48+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:00:03+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:00:07+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:00:20+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:00:24+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:00:36+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:00:40+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:00:51+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:00:54+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:01:05+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:01:08+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:01:17+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:29:19+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:29:23+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:29:37+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:29:42+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:30:02+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:30:16+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:30:26+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:30:35+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:30:49+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:30:57+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:31:06+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:31:15+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:32:55+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:33:04+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:33:13+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:33:22+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:33:30+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:33:39+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:33:49+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:33:57+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:34:06+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:35:59+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:36:07+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:36:17+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:36:26+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:36:34+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:36:42+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:36:50+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:36:59+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:37:08+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:37:18+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:37:27+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:37:37+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:37:46+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:38:02+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:38:11+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:38:20+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:38:29+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:38:39+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:38:48+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:38:57+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:39:42+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:39:50+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:40:05+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:40:14+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:40:23+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:40:31+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:40:39+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:40:47+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:40:55+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:41:04+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:42:36+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:42:45+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:54:30+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:54:39+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:54:48+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:54:58+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:55:07+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:55:16+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:55:25+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:55:34+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:55:43+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:55:53+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:55:58+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:56:07+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:56:20+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:56:33+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:56:42+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:58:38+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T22:59:20+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T23:15:38+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T23:22:08+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T23:26:28+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-22T23:26:46+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:08:17+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:08:25+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:08:33+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:08:40+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:08:49+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:08:57+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:09:05+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:09:13+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:09:20+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:09:34+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:09:43+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:09:50+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:09:59+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:10:10+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:10:17+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:10:25+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:10:33+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:10:40+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:10:47+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:10:55+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:11:03+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:11:12+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:11:20+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:11:29+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:11:37+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:11:45+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:11:52+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:12:00+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:12:11+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:12:52+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:12:58+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:13:05+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:13:14+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:13:22+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:13:30+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:13:38+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:13:45+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:13:52+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:13:59+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:16:01+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:16:13+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:16:22+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:16:33+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:20:56+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:21:04+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:21:11+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:21:18+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:21:30+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:21:38+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T00:21:45+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:10:53+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:11:02+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:11:10+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:11:17+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:11:25+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:11:33+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:11:45+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:11:53+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:12:00+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:12:07+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:12:12+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:12:19+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:12:26+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:12:34+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:12:42+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:12:51+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:12:58+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:13:05+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:13:13+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:13:22+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:13:41+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:13:53+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:14:01+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:14:09+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:14:17+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:14:25+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:14:33+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:14:41+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:14:50+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:14:59+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:15:34+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:15:44+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:15:59+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:16:08+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:16:17+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"},{\"timestamp\":\"2025-09-23T02:16:27+00:00\",\"action\":\"application_continued\",\"ip\":\"172.18.0.1\"}]"', '2025-09-22 16:11:27', '2025-09-23 11:05:04', NULL, NULL, NULL, '{"transcript":{"name":"unofficial_transcript_25000001_2025-09-07.pdf","path":"applications\/55\/documents\/qB0FaJvL19P56MMILHaeZHtCJWBaxmqHcvZiByVn.pdf"},"resume":{"id":54,"name":"doc (1).png","path":"applications\/55\/documents\/JKt7OS699BzIDiu2a0ZyBpZPLhvKOzZZsFKFKupA.png","size":14838,"uploaded_at":"2025-09-23T10:54:35+00:00","status":"uploaded"},"personal_statement":{"id":55,"name":"DWF.jpeg","path":"applications\/55\/documents\/Ob3Bh7VirfEF2RIfewHflXV18vBAkZ2qxaDEkfQf.jpg","size":398593,"uploaded_at":"2025-09-23T11:04:21+00:00","status":"uploaded"},"passport":{"id":56,"name":"Native.png","path":"applications\/55\/documents\/ZSxnMaYfhIOevdsnlQKPbBDyio4xsBrchhjeEIuh.png","size":4396,"uploaded_at":"2025-09-23T11:05:04+00:00","status":"uploaded"}}', NULL);
INSERT INTO public.admission_applications VALUES (38, 'APP-2025-000016', '3bbd920f-f5ce-4371-8691-c7555620e72e', 'Alda', 'Sarai', 'Hand', NULL, '2003-01-17', 'female', 'Wallis and Futuna', NULL, NULL, '680495824485', 'ewell.vonrueden@example.org', '1-559-415-8627', NULL, '252 King Turnpike', '87555 Tromp Road', 'Lake Emmy', 'Michigan', '03226', 'Turks and Caicos Islands', 'Lessie Blanda', 'Friend', '743-983-8905', 'bhyatt@example.com', NULL, NULL, NULL, NULL, NULL, 'freshman', 1, 2, NULL, NULL, NULL, 'fall', 2025, 'Schuppe and Sons University', 'Syrian Arab Republic', NULL, NULL, NULL, 3.24, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"SAT":{"total":1301,"math":725,"verbal":670,"test_date":"2024-11-02"}}', 'Perspiciatis fugit odio et animi. Placeat aut et quidem saepe id sint. Non excepturi et corrupti est quia. Omnis exercitationem iure nihil temporibus modi et temporibus.

Officia laborum dolorem vero ad est eligendi. Facilis quas corrupti quia est repellat voluptas. Aut in quam maiores quo.

Numquam voluptatum non quod doloribus. Veritatis et earum assumenda cum aut veniam minus. Voluptas modi nihil soluta at asperiores.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'decision_pending', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-08-02', NULL, false, false, NULL, '2025-06-22 13:42:09', '2025-08-27 12:16:48', NULL, '2025-09-17 15:38:47', '2025-12-11 15:49:18', 192, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:47', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (40, 'APP-2025-000018', 'e24c5048-53ec-49e1-8068-b2a4e3ac17b4', 'Clay', 'Adam', 'Schmidt', NULL, '2004-01-23', 'other', 'Venezuela', NULL, NULL, '229869683629', 'william.blick@example.org', '1-534-559-2079', NULL, '8827 Denesik Walks Suite 253', '39881 Melvin Port Suite 926', 'Eldonton', 'Utah', '36844', 'Bolivia', 'Prof. Kurt Green', 'Friend', '+19384628431', 'dleannon@example.com', NULL, NULL, NULL, NULL, NULL, 'graduate', 1, 1, NULL, NULL, NULL, 'fall', 2025, 'Steuber Group University', 'Greece', NULL, NULL, NULL, 3.31, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"GRE":{"verbal":155,"quantitative":147,"analytical":5.2,"test_date":"2024-12-12"}}', 'Voluptatum hic iste ducimus omnis sint sed. Tenetur repudiandae numquam quod quasi similique provident. Optio enim dolorem ea sed id molestias assumenda. Iusto laboriosam nostrum magni voluptatibus deserunt.

Reprehenderit repellendus optio rerum assumenda quam. Amet cupiditate soluta consequuntur odit culpa nisi ea. Et incidunt libero vitae explicabo tempore. Consequatur itaque voluptas est et cum soluta vero consectetur.

Suscipit eum aut qui voluptates earum id. Temporibus sint laudantium mollitia animi sed qui. Tempore sint quas exercitationem pariatur reiciendis deleniti.', 'Aut assumenda qui consectetur repellat aliquid dignissimos cupiditate. Aperiam hic omnis soluta labore tenetur corporis ex. Dolor dolores voluptas placeat reprehenderit. Nesciunt distinctio ipsa maiores.

Id debitis qui corrupti qui facilis aut. Molestias dolorum voluptatum vel quasi quisquam doloremque. Ipsa et et ipsum rerum quos repellat. Officia laboriosam officia ea cupiditate doloremque velit tenetur.

Ipsam ea ut voluptates eveniet doloribus voluptatum illum. Aut esse accusamus dolore neque quas molestiae. Dignissimos cupiditate repudiandae dolores at vel dignissimos qui. Consequatur nihil est consequatur est.

Sint dolores necessitatibus mollitia architecto voluptas ipsum et. Sed perspiciatis incidunt enim ducimus in. Est aut eveniet eum aut et. Sapiente aut odit fuga quaerat.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admitted', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-07-22', NULL, false, false, NULL, '2025-06-19 03:22:25', '2025-08-14 22:32:35', NULL, '2025-09-17 15:38:47', '2025-12-11 15:49:18', 194, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:47', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (41, 'APP-2025-000019', 'c55fd68a-c634-4597-8985-350182be9452', 'Celia', 'Naomi', 'Kshlerin', NULL, '2001-06-14', 'female', 'Lithuania', NULL, NULL, '261745913669', 'maxime54@example.net', '754-345-6076', '1-678-655-7907', '5936 Trisha Union Apt. 345', '46780 Conroy Crossroad', 'Olgafurt', 'Ohio', '01852-8813', 'Indonesia', 'Brady Swaniawski', 'Sibling', '1-813-871-3490', 'larson.marques@example.org', NULL, NULL, NULL, NULL, NULL, 'freshman', 1, 2, NULL, NULL, NULL, 'fall', 2025, 'Oberbrunner, Jenkins and Feil University', 'Tanzania', NULL, NULL, NULL, 3.38, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"SAT":{"total":1438,"math":658,"verbal":594,"test_date":"2024-10-15"}}', 'Laudantium amet nemo dolore voluptates quia tempora. Dolorem iure architecto id ratione natus.

Qui praesentium ad doloremque maiores suscipit. Aut quo aperiam reprehenderit. Dignissimos consectetur saepe omnis. Rem quisquam inventore sed pariatur.

Veniam nihil et sit deleniti eligendi ut reiciendis. Explicabo voluptas eaque qui voluptatem non animi non. Quisquam asperiores natus voluptatem est odit. Accusamus quia et perferendis voluptas earum nihil.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admitted', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-08-04', NULL, false, false, NULL, '2025-08-08 10:08:17', '2025-08-25 03:08:25', NULL, '2025-09-17 15:38:48', '2025-12-11 15:49:18', 195, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:48', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (42, 'APP-2025-000020', '3f4ffc15-a662-4e9d-b5dc-5c13fcf65ac3', 'Cali', 'Maeve', 'Kovacek', NULL, '2000-10-01', 'male', 'Hong Kong', NULL, 'ZA093106', NULL, 'rosemary91@example.com', '352.579.7995', '+1-360-822-3465', '6716 Heidenreich Walk', '6108 Louvenia Union', 'East Amietown', 'Florida', '67058-9892', 'Cameroon', 'Santos Leuschke', 'Sibling', '+1-781-802-8045', 'tfadel@example.com', NULL, NULL, NULL, NULL, NULL, 'international', 1, 2, NULL, NULL, NULL, 'fall', 2025, 'VonRueden, Schultz and Green University', 'Cook Islands', NULL, NULL, NULL, 3.82, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"TOEFL":{"total":109,"test_date":"2025-02-18"}}', 'Laboriosam iure voluptatem vitae beatae. Asperiores et voluptates non nulla. At repudiandae tenetur ratione. Ut autem eius magnam iusto.

Velit facere earum numquam est. Tenetur explicabo repudiandae eum laborum consectetur quia voluptatem. Inventore expedita asperiores nihil itaque ab perspiciatis dolores. Enim facere rerum repudiandae et deleniti delectus explicabo laudantium.

Deleniti dicta perferendis recusandae quaerat iste ad. In necessitatibus voluptas saepe voluptas non inventore.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admitted', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-09-10', NULL, false, false, NULL, '2025-08-22 01:46:08', '2025-09-07 21:18:27', NULL, '2025-09-17 15:38:48', '2025-12-11 15:49:18', 196, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:48', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (30, 'APP-2025-000008', 'a0104e63-4fe8-4dba-8567-093f6232ba1e', 'Cassandre', 'Hannah', 'Hayes', NULL, '2006-10-30', 'other', 'Chad', NULL, NULL, '717965574395', 'dejah.stroman@example.com', '302.353.1587', NULL, '6778 Abe Cove Apt. 724', '40742 Schulist Stream Suite 027', 'East Howardshire', 'Michigan', '69451', 'Nigeria', 'Camille Schroeder', 'Spouse', '539.973.5119', 'uabernathy@example.com', NULL, NULL, NULL, NULL, NULL, 'transfer', 1, 2, NULL, NULL, NULL, 'fall', 2025, 'Hahn-O''Connell University', 'Korea', NULL, NULL, NULL, 2.62, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '[]', 'Cum sed ab non magni a pariatur ut. Dolor nihil quia reiciendis architecto. Aspernatur omnis nulla facilis blanditiis rerum quod. Maxime nulla sit maxime omnis.

Quis sint delectus perferendis repellat. Commodi commodi omnis dolores quod culpa rerum et et. Quae optio est dolor. Aut quo modi ab tenetur enim sunt.

Rerum natus aperiam aut eos consequatur explicabo saepe. Voluptas mollitia excepturi quibusdam officia quod dolorem. Suscipit laborum voluptatum quia minus odio voluptatem. Eos aut dolorem non magnam repellat eum nobis velit. Est voluptatem corrupti dicta earum qui.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'under_review', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-07-21', NULL, false, false, NULL, '2025-08-19 00:27:23', '2025-08-29 12:02:11', NULL, '2025-09-17 15:38:42', '2025-12-11 15:49:17', 175, NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-17 15:38:42', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (43, 'APP-2025-000021', 'b5dd7836-f950-42af-bcfa-7caf90359af1', 'Jaiden', 'Randy', 'Watsica', NULL, '2000-09-29', 'female', 'Zimbabwe', NULL, 'GZ757735', NULL, 'barrows.rowena@example.org', '310-988-1977', NULL, '6404 Louisa Summit', '6908 Braulio Inlet', 'Port Willieshire', 'New Hampshire', '63019-3082', 'Serbia', 'Ellsworth Hintz DVM', 'Spouse', '(305) 286-2335', 'bkozey@example.org', NULL, NULL, NULL, NULL, NULL, 'international', 1, 1, NULL, NULL, NULL, 'fall', 2025, 'Mosciski-Stark University', 'Niger', NULL, NULL, NULL, 3.26, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"TOEFL":{"total":71,"test_date":"2025-01-12"}}', 'Sit quia consequatur ipsa sed optio. Beatae alias eum magnam est nam nulla. Sed quia ea dolores in nostrum et dolor dolores. Facilis maxime qui earum quae voluptatem beatae.

Quia at officia aliquid aut voluptas non et. Quod quo reiciendis corrupti vel voluptatem et sit voluptatem. Asperiores dolorum nihil magni dolorem ut. Quo sint necessitatibus est et.

Unde molestiae odit et voluptatem non. Omnis reiciendis delectus et et. Soluta molestiae culpa mollitia nesciunt.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'waitlisted', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-08-07', NULL, false, false, NULL, '2025-07-03 18:02:19', '2025-08-26 08:00:33', NULL, '2025-09-17 15:38:43', '2025-12-11 15:49:18', 176, NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-17 15:38:43', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (23, 'APP-2025-000001', 'b18e545c-e52a-4cb3-8524-7d89575216d7', 'Autumn', NULL, 'Heathcote', NULL, '1997-04-22', 'female', 'Tokelau', NULL, NULL, '888706727595', 'boehm.cecilia@example.com', '559-834-9230', NULL, '85243 Johnston Falls', '439 Kiel Corners', 'Beierland', 'Indiana', '59339-5824', 'Saint Lucia', 'Cordia Kilback', 'Friend', '816.214.9227', 'eldon12@example.com', NULL, NULL, NULL, NULL, NULL, 'transfer', 1, 1, NULL, NULL, NULL, 'fall', 2025, 'Murazik Group University', 'Nigeria', NULL, NULL, NULL, 3.12, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '[]', 'Vero et delectus nisi assumenda. Impedit eum numquam dolor et recusandae quia aut. Quia iste repudiandae reiciendis sapiente. Minima fugiat vero ut asperiores et voluptatum.

Eum facere illum ut excepturi voluptatem. Sint assumenda excepturi quisquam in. Ut doloribus odit recusandae aliquid nihil.

Est ut praesentium dolorem voluptate ut consequuntur omnis. Voluptatem quasi et unde voluptatibus maiores at aut quidem. Veritatis reiciendis quis aliquam doloremque quis dolor.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'draft', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, false, 75.00, NULL, NULL, false, false, NULL, '2025-07-30 12:35:47', NULL, NULL, '2025-09-17 15:38:43', '2025-12-11 15:49:17', 177, NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-17 15:38:43', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (25, 'APP-2025-000003', 'b49e22cb-2e34-4b33-9354-1b95cecfef10', 'Arianna', 'Allison', 'Schuppe', NULL, '2002-09-13', 'other', 'South Africa', NULL, NULL, '975912366909', 'alden.konopelski@example.com', '(351) 515-8521', '1-251-628-4067', '72936 Tremblay Islands', '69962 Hane Parkways', 'Port Levishire', 'South Carolina', '90578-6676', 'China', 'Santino Grady', 'Spouse', '480-255-7906', 'pacocha.francisca@example.com', NULL, NULL, NULL, NULL, NULL, 'graduate', 1, 1, NULL, NULL, NULL, 'fall', 2025, 'O''Conner-Shanahan University', 'Nepal', NULL, NULL, NULL, 3.44, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '{"GRE":{"verbal":158,"quantitative":148,"analytical":5.5,"test_date":"2024-06-06"}}', 'Ipsum praesentium quae et corporis omnis. Magnam ullam veniam dolor ratione. Adipisci qui illo nihil sit in qui. Dolorum nisi assumenda doloribus recusandae provident corrupti sint aliquam.

Natus ut ut doloribus incidunt nesciunt. Corrupti voluptate nostrum magni est repudiandae. Ipsa et ea porro voluptate. Sed perferendis qui iste voluptate totam molestiae magnam amet. Odio quas eos vel eaque quibusdam.

Est qui consequatur quaerat suscipit rem. Est modi ut quas et atque. Tenetur modi inventore modi cumque ut.', 'Et quia commodi ut vel. Modi et consectetur deserunt ex nobis dolor cumque. Nihil cupiditate sapiente molestias sequi. Enim illum qui nemo voluptatem aut beatae aut. Nisi qui iste perferendis dolore sed.

Aut illo hic similique aliquid earum sequi. Magnam delectus quisquam corporis nostrum et porro odit. Voluptatum non voluptas est temporibus et sapiente.

Voluptate eos inventore voluptatibus ipsam. Quia dolorem error fugiat voluptas ab quasi. Deleniti et nobis rerum. Vel aut consequatur aut autem.

Aliquid quia aut incidunt eaque placeat. Dolorum quibusdam blanditiis provident accusantium.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'submitted', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-08-13', NULL, false, false, NULL, '2025-06-24 21:19:02', '2025-08-21 18:39:51', NULL, '2025-09-17 15:38:44', '2025-12-11 15:49:17', 179, NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-17 15:38:44', NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.admission_applications VALUES (27, 'APP-2025-000005', '1180476b-ce1c-46d9-b33d-5d3b1f4c5b41', 'Tiara', NULL, 'McLaughlin', NULL, '1998-09-23', 'female', 'Kazakhstan', NULL, NULL, '456134754730', 'mlangworth@example.net', '+19103904067', '541-252-5237', '872 Kihn Point', '80635 Durward Fall', 'Port Ursulamouth', 'Missouri', '49518', 'Gambia', 'Rogelio Mraz', 'Sibling', '802-425-4507', 'dibbert.baby@example.net', NULL, NULL, NULL, NULL, NULL, 'transfer', 1, 2, NULL, NULL, NULL, 'fall', 2025, 'Dickens PLC University', 'Niger', NULL, NULL, NULL, 2.66, '4.0', NULL, NULL, NULL, NULL, NULL, NULL, '[]', 'Vitae dolor aut occaecati aspernatur dolor. Et nam eligendi consectetur rerum provident voluptas. Eligendi nulla consequatur qui et optio porro molestiae ipsum. Et dolorem voluptatibus exercitationem aperiam fugiat.

Qui harum fugit occaecati sunt necessitatibus sint. Quis quod ipsam commodi nulla. Ab nostrum explicabo qui itaque qui.

Cupiditate omnis totam laboriosam reiciendis magni consectetur. Voluptatum et et quo nobis. Enim esse nobis aut molestiae expedita. Corporis quia ratione numquam.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'submitted', NULL, NULL, NULL, NULL, NULL, false, NULL, false, NULL, NULL, NULL, NULL, false, NULL, NULL, true, 75.00, '2025-08-10', NULL, false, false, NULL, '2025-06-16 02:56:37', '2025-09-02 17:42:54', NULL, '2025-09-17 15:38:44', '2025-12-11 15:49:17', 181, NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-17 15:38:44', NULL, NULL, NULL, NULL, NULL);


--
-- Data for Name: admission_interviews; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: admission_settings; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.admission_settings VALUES (5, 1, 1, '2025-08-12', '2025-11-12', '2025-12-12', '2026-01-12', 75.00, 500.00, 1000, 200, '"[\"transcript\",\"personal_statement\",\"recommendation_letter\",\"test_scores\"]"', NULL, true, '2025-09-12 15:49:16', '2025-09-12 15:49:16');
INSERT INTO public.admission_settings VALUES (6, 1, 2, '2025-08-12', '2025-11-12', '2025-12-12', '2026-01-12', 100.00, 200.00, 1000, 200, '"[\"transcript\",\"personal_statement\",\"recommendation_letter\",\"test_scores\"]"', NULL, true, '2025-09-12 15:49:16', '2025-09-12 15:49:16');
INSERT INTO public.admission_settings VALUES (7, 1, 5, '2025-08-12', '2025-11-12', '2025-12-12', '2026-01-12', 50.00, 300.00, 1000, 200, '"[\"transcript\",\"personal_statement\",\"recommendation_letter\",\"test_scores\"]"', NULL, true, '2025-09-12 15:49:16', '2025-09-12 15:49:16');
INSERT INTO public.admission_settings VALUES (8, 1, 6, '2025-08-12', '2025-11-12', '2025-12-12', '2026-01-12', 100.00, 300.00, 1000, 200, '"[\"transcript\",\"personal_statement\",\"recommendation_letter\",\"test_scores\"]"', NULL, true, '2025-09-12 15:49:16', '2025-09-12 15:49:16');


--
-- Data for Name: admission_waitlists; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: colleges; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.colleges VALUES (6, 'CAS', 'College of Arts and Sciences', 'Liberal arts and sciences education', 'academic', 1, 2, 'cas@university.edu', '555-0100', NULL, 'Liberal Arts Building', NULL, true, NULL, NULL, NULL, '2025-09-04 22:28:47', '2025-09-04 22:28:49', NULL);
INSERT INTO public.colleges VALUES (7, 'COE', 'College of Engineering', 'Engineering and technology programs', 'academic', 3, 4, 'engineering@university.edu', '555-0200', NULL, 'Engineering Complex', NULL, true, NULL, NULL, NULL, '2025-09-04 22:28:47', '2025-09-04 22:28:49', NULL);
INSERT INTO public.colleges VALUES (8, 'COB', 'College of Business', 'Business and management education', 'professional', 5, 6, 'business@university.edu', '555-0300', NULL, 'Business Tower', NULL, true, NULL, NULL, NULL, '2025-09-04 22:28:47', '2025-09-04 22:28:50', NULL);
INSERT INTO public.colleges VALUES (9, 'COM', 'College of Medicine', 'Medical and health sciences', 'professional', 7, 8, 'medicine@university.edu', '555-0400', NULL, 'Medical Center', NULL, true, NULL, NULL, NULL, '2025-09-04 22:28:47', '2025-09-04 22:28:50', NULL);


--
-- Data for Name: schools; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.schools VALUES (3, 'SCS', 'School of Computer Science', 'Computer science and information technology', 7, 9, 'cs@university.edu', '555-0210', NULL, 'Technology Building', true, NULL, NULL, '2025-09-04 22:28:47', '2025-09-04 22:28:50', NULL);
INSERT INTO public.schools VALUES (4, 'SNS', 'School of Nursing', 'Nursing and healthcare programs', 9, 10, 'nursing@university.edu', '555-0410', NULL, 'Health Sciences Building', true, NULL, NULL, '2025-09-04 22:28:47', '2025-09-04 22:28:51', NULL);


--
-- Data for Name: departments; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.departments VALUES (7, 'ECE', 'Department of Electrical and Computer Engineering', 'Electrical and computer engineering programs', NULL, 'ece@university.edu', '555-0220', true, '2025-09-04 22:28:47', '2025-09-04 22:28:51', 'academic', 7, NULL, NULL, 14, NULL, NULL, NULL, NULL, 'Engineering Building A', 'Room 100', 0, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (8, 'MECH', 'Department of Mechanical Engineering', 'Mechanical engineering programs', NULL, 'mech@university.edu', '555-0230', true, '2025-09-04 22:28:47', '2025-09-04 22:28:52', 'academic', 7, NULL, NULL, 15, NULL, NULL, NULL, NULL, 'Engineering Building B', 'Room 200', 0, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (9, 'CS', 'Department of Computer Science', 'Computer science and software engineering', NULL, 'cs.dept@university.edu', '555-0211', true, '2025-09-04 22:28:47', '2025-09-04 22:28:52', 'academic', NULL, 3, NULL, 16, NULL, NULL, NULL, NULL, 'Technology Building', 'Room 500', 0, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (10, 'ACCT', 'Department of Accounting', 'Accounting and finance programs', NULL, 'accounting@university.edu', '555-0310', true, '2025-09-04 22:28:47', '2025-09-04 22:28:52', 'academic', 8, NULL, NULL, 17, NULL, NULL, NULL, NULL, 'Business Tower', 'Floor 10', 0, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (11, 'MGMT', 'Department of Management', 'Management and organizational behavior', NULL, 'management@university.edu', '555-0320', true, '2025-09-04 22:28:47', '2025-09-04 22:28:52', 'academic', 8, NULL, NULL, 18, NULL, NULL, NULL, NULL, 'Business Tower', 'Floor 12', 0, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (12, 'COMP', 'Computer Science', 'Department of Computer Science', NULL, NULL, NULL, true, '2025-09-06 14:05:16', '2025-09-06 14:05:16', 'academic', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (13, 'BUSI', 'Business Administration', 'Department of Business Administration', NULL, NULL, NULL, true, '2025-09-06 14:05:16', '2025-09-06 14:05:16', 'academic', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (14, 'INFO', 'Information Technology', 'Department of Information Technology', NULL, NULL, NULL, true, '2025-09-06 14:05:16', '2025-09-06 14:05:16', 'academic', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (15, 'NURS', 'Nursing', 'Department of Nursing', NULL, NULL, NULL, true, '2025-09-06 14:05:16', '2025-09-06 14:05:16', 'academic', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (18, 'CHEM', 'Chemistry', 'Department of Chemistry', NULL, NULL, NULL, true, '2025-09-06 14:10:49', '2025-09-06 14:10:49', 'academic', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (6, 'ENGL', 'English', 'Department of English', NULL, 'english@university.edu', '555-0130', true, '2025-09-04 22:28:47', '2025-09-06 17:58:50', 'academic', 6, NULL, NULL, 13, NULL, NULL, NULL, NULL, 'Liberal Arts Building', 'Room 400', 11, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (4, 'MATH', 'Mathematics', 'Department of Mathematics', NULL, 'math@university.edu', '555-0110', true, '2025-09-04 22:28:47', '2025-09-06 17:58:50', 'academic', 6, NULL, NULL, 11, NULL, NULL, NULL, NULL, 'Mathematics Building', 'Room 200', 8, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);
INSERT INTO public.departments VALUES (5, 'PHYS', 'Physics', 'Department of Physics', NULL, 'physics@university.edu', '555-0120', true, '2025-09-04 22:28:47', '2025-09-06 17:58:50', 'academic', 6, NULL, NULL, 12, NULL, NULL, NULL, NULL, 'Science Building', 'Room 300', 9, 0, 0, 0, NULL, NULL, true, true, NULL, NULL, NULL, NULL);


--
-- Data for Name: divisions; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.divisions VALUES (1, 'AI', 'Artificial Intelligence Division', 'AI and machine learning research', 9, NULL, true, NULL, NULL, '2025-09-04 22:28:47', '2025-09-04 22:28:47', NULL);
INSERT INTO public.divisions VALUES (2, 'SE', 'Software Engineering Division', 'Software development and engineering', 9, NULL, true, NULL, NULL, '2025-09-04 22:28:47', '2025-09-04 22:28:47', NULL);
INSERT INTO public.divisions VALUES (3, 'STAT', 'Statistics Division', 'Applied and theoretical statistics', 4, NULL, true, NULL, NULL, '2025-09-04 22:28:47', '2025-09-04 22:28:47', NULL);


--
-- Data for Name: courses; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.courses VALUES (1, 'CS101', 'Introduction to Computer Science', 'This course covers the fundamental concepts of Introduction to Computer Science.', 3, 3, 0, 0, 100, 'Computer Science', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 13:14:16', '2025-09-06 14:10:49', NULL, 12, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (3, 'CS102', 'Programming Fundamentals', 'This course covers the fundamental concepts of Programming Fundamentals.', 4, 3, 0, 0, 100, 'Computer Science', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, true, false, true, 5, 30, NULL, '2025-09-06 17:34:49', '2025-09-06 17:34:49', NULL, 12, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (4, 'CS201', 'Data Structures', 'This course covers the fundamental concepts of Data Structures.', 3, 3, 0, 0, 200, 'Computer Science', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:49', '2025-09-06 17:58:49', NULL, 12, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (5, 'CS202', 'Algorithms', 'This course covers the fundamental concepts of Algorithms.', 3, 3, 0, 0, 200, 'Computer Science', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 12, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (6, 'CS301', 'Database Systems', 'This course covers the fundamental concepts of Database Systems.', 3, 3, 0, 0, 300, 'Computer Science', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 12, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (7, 'CS302', 'Software Engineering', 'This course covers the fundamental concepts of Software Engineering.', 3, 3, 0, 0, 300, 'Computer Science', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 12, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (8, 'CS303', 'Web Development', 'This course covers the fundamental concepts of Web Development.', 3, 3, 0, 0, 300, 'Computer Science', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, true, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 12, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (9, 'CS401', 'Artificial Intelligence', 'This course covers the fundamental concepts of Artificial Intelligence.', 3, 3, 0, 0, 400, 'Computer Science', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 12, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (10, 'BUS101', 'Introduction to Business', 'This course covers the fundamental concepts of Introduction to Business.', 3, 3, 0, 0, 100, 'Business Administration', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 13, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (11, 'BUS102', 'Principles of Management', 'This course covers the fundamental concepts of Principles of Management.', 3, 3, 0, 0, 100, 'Business Administration', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 13, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (12, 'ACC101', 'Financial Accounting', 'This course covers the fundamental concepts of Financial Accounting.', 3, 3, 0, 0, 100, 'Business Administration', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 13, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (13, 'MKT201', 'Marketing Principles', 'This course covers the fundamental concepts of Marketing Principles.', 3, 3, 0, 0, 200, 'Business Administration', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 13, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (14, 'FIN201', 'Corporate Finance', 'This course covers the fundamental concepts of Corporate Finance.', 3, 3, 0, 0, 200, 'Business Administration', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 13, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (15, 'BUS301', 'Business Ethics', 'This course covers the fundamental concepts of Business Ethics.', 3, 3, 0, 0, 300, 'Business Administration', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 13, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (16, 'ENG101', 'English Composition I', 'This course covers the fundamental concepts of English Composition I.', 3, 3, 0, 0, 100, 'English', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 6, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (17, 'ENG102', 'English Composition II', 'This course covers the fundamental concepts of English Composition II.', 3, 3, 0, 0, 100, 'English', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 6, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (18, 'MATH101', 'Calculus I', 'This course covers the fundamental concepts of Calculus I.', 4, 3, 0, 0, 100, 'Mathematics', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 4, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (19, 'MATH102', 'Calculus II', 'This course covers the fundamental concepts of Calculus II.', 4, 3, 0, 0, 100, 'Mathematics', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, false, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 4, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (20, 'PHY101', 'Physics I', 'This course covers the fundamental concepts of Physics I.', 4, 3, 0, 0, 100, 'Physics', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, true, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 5, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.courses VALUES (21, 'CHEM101', 'Chemistry I', 'This course covers the fundamental concepts of Chemistry I.', 4, 3, 0, 0, 100, 'Chemistry', 'core', 'letter', NULL, NULL, NULL, NULL, 0.00, 0.00, true, false, true, 5, 30, NULL, '2025-09-06 17:58:50', '2025-09-06 17:58:50', NULL, 18, NULL, NULL, NULL, NULL, NULL);


--
-- Data for Name: course_sections; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.course_sections VALUES (1, '10004', 1, 1, '01', 11, 'traditional', 22, 0, 5, 0, 'open', 'TR', '08:00:00', '09:50:00', 'Room 101', 'Engineering Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (2, '10035', 3, 1, '01', 47, 'traditional', 38, 0, 5, 0, 'open', 'MWF', '11:00:00', '09:50:00', 'Room 102', 'Engineering Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (3, '10037', 4, 1, '01', 12, 'hybrid', 22, 0, 5, 0, 'open', 'TR', '13:00:00', '09:50:00', 'Room 103', 'Science Hall', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (4, '10015', 5, 1, '01', 46, 'traditional', 35, 0, 5, 0, 'open', 'TTh', '09:00:00', '09:50:00', 'Room 104', 'Main Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (5, '10023', 6, 1, '01', 16, 'online_sync', 30, 0, 5, 0, 'open', 'MWF', '10:00:00', '09:50:00', 'Room 105', 'Engineering Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (6, '10065', 7, 1, '01', 12, 'hybrid', 33, 0, 5, 0, 'open', 'TTh', '13:00:00', '09:50:00', 'Room 106', 'Science Hall', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (7, '10075', 8, 1, '01', 19, 'online_sync', 27, 0, 5, 0, 'open', 'MWF', '08:00:00', '09:50:00', 'Room 107', 'Main Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (8, '10066', 9, 1, '01', 2, 'hybrid', 35, 0, 5, 0, 'open', 'MWF', '09:00:00', '09:50:00', 'Room 108', 'Engineering Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (9, '10081', 10, 1, '01', 12, 'traditional', 28, 0, 5, 0, 'open', 'MW', '11:00:00', '09:50:00', 'Room 109', 'Science Hall', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (10, '10062', 11, 1, '01', 33, 'online_sync', 29, 0, 5, 0, 'open', 'MWF', '08:00:00', '09:50:00', 'Room 110', 'Engineering Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (11, '10056', 12, 1, '01', 15, 'online_sync', 30, 0, 5, 0, 'open', 'TTh', '13:00:00', '09:50:00', 'Room 111', 'Engineering Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51', NULL, NULL);
INSERT INTO public.course_sections VALUES (13, '10012', 13, 1, '01', 33, 'hybrid', 30, 0, 5, 0, 'open', 'TR', '09:00:00', '09:50:00', 'Room 112', 'Main Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13', NULL, NULL);
INSERT INTO public.course_sections VALUES (14, '10013', 14, 1, '01', 35, 'online_sync', 38, 0, 5, 0, 'open', 'MW', '10:00:00', '09:50:00', 'Room 113', 'Main Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13', NULL, NULL);
INSERT INTO public.course_sections VALUES (15, '10014', 15, 1, '01', 6, 'hybrid', 39, 0, 5, 0, 'open', 'MW', '10:00:00', '09:50:00', 'Room 114', 'Main Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13', NULL, NULL);
INSERT INTO public.course_sections VALUES (16, '10016', 16, 1, '01', 13, 'online_sync', 34, 0, 5, 0, 'open', 'MWF', '10:00:00', '09:50:00', 'Room 115', 'Engineering Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13', NULL, NULL);
INSERT INTO public.course_sections VALUES (17, '10017', 17, 1, '01', 6, 'traditional', 22, 0, 5, 0, 'open', 'MW', '11:00:00', '09:50:00', 'Room 116', 'Main Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13', NULL, NULL);
INSERT INTO public.course_sections VALUES (18, '10018', 18, 1, '01', 13, 'traditional', 24, 0, 5, 0, 'open', 'TTh', '13:00:00', '09:50:00', 'Room 117', 'Engineering Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13', NULL, NULL);
INSERT INTO public.course_sections VALUES (19, '10019', 19, 1, '01', 46, 'traditional', 24, 0, 5, 0, 'open', 'TR', '13:00:00', '09:50:00', 'Room 118', 'Engineering Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13', NULL, NULL);
INSERT INTO public.course_sections VALUES (20, '10020', 20, 1, '01', 6, 'hybrid', 20, 0, 5, 0, 'open', 'MW', '13:00:00', '09:50:00', 'Room 119', 'Main Building', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13', NULL, NULL);
INSERT INTO public.course_sections VALUES (21, '10021', 21, 1, '01', 32, 'hybrid', 27, 0, 5, 0, 'open', 'MWF', '10:00:00', '09:50:00', 'Room 120', 'Science Hall', NULL, NULL, NULL, false, NULL, NULL, 0.00, NULL, NULL, NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13', NULL, NULL);


--
-- Data for Name: announcements; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: entrance_exams; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: entrance_exam_registrations; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: exam_centers; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: exam_sessions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: exam_question_papers; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: exam_answer_keys; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: exam_questions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: answer_key_challenges; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: application_checklist_items; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.application_checklist_items VALUES (177, 23, 'Personal Information', 'form', true, true, '2025-09-12 15:49:17', NULL, 1, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (178, 23, 'Educational Background', 'form', true, true, '2025-09-12 15:49:17', NULL, 2, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (180, 24, 'Personal Information', 'form', true, true, '2025-09-12 15:49:17', NULL, 1, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (181, 24, 'Educational Background', 'form', true, true, '2025-09-12 15:49:17', NULL, 2, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (301, 56, 'Application Form', 'form', true, true, '2025-09-22 17:27:25', NULL, 1, '2025-09-25 17:27:25', '2025-09-25 17:27:25');
INSERT INTO public.application_checklist_items VALUES (183, 25, 'Personal Information', 'form', true, true, '2025-09-12 15:49:17', NULL, 1, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (184, 25, 'Educational Background', 'form', true, true, '2025-09-12 15:49:17', NULL, 2, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (302, 56, 'WASSCE Certificate', 'document', true, true, '2025-09-23 17:27:25', NULL, 3, '2025-09-25 17:27:25', '2025-09-25 17:27:25');
INSERT INTO public.application_checklist_items VALUES (186, 26, 'Personal Information', 'form', true, true, '2025-09-12 15:49:17', NULL, 1, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (187, 26, 'Educational Background', 'form', true, true, '2025-09-12 15:49:17', NULL, 2, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (303, 56, 'WASSCE Results Statement', 'document', true, true, '2025-09-23 17:27:25', NULL, 4, '2025-09-25 17:27:25', '2025-09-25 17:27:25');
INSERT INTO public.application_checklist_items VALUES (189, 27, 'Personal Information', 'form', true, true, '2025-09-12 15:49:17', NULL, 1, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (190, 27, 'Educational Background', 'form', true, true, '2025-09-12 15:49:17', NULL, 2, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (304, 56, 'Teacher Recommendations (2)', 'document', true, true, '2025-09-24 17:27:25', NULL, 6, '2025-09-25 17:27:25', '2025-09-25 17:27:25');
INSERT INTO public.application_checklist_items VALUES (192, 28, 'Personal Information', 'form', true, true, '2025-09-12 15:49:17', NULL, 1, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (193, 28, 'Educational Background', 'form', true, true, '2025-09-12 15:49:17', NULL, 2, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (305, 56, 'Birth Certificate', 'document', true, true, '2025-09-22 17:27:25', NULL, 7, '2025-09-25 17:27:25', '2025-09-25 17:27:25');
INSERT INTO public.application_checklist_items VALUES (195, 29, 'Personal Information', 'form', true, true, '2025-09-12 15:49:17', NULL, 1, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (196, 29, 'Educational Background', 'form', true, true, '2025-09-12 15:49:17', NULL, 2, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (306, 56, 'Passport Photograph', 'document', true, true, '2025-09-22 17:27:25', NULL, 8, '2025-09-25 17:27:25', '2025-09-25 17:27:25');
INSERT INTO public.application_checklist_items VALUES (198, 30, 'Personal Information', 'form', true, true, '2025-09-12 15:49:17', NULL, 1, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (199, 30, 'Educational Background', 'form', true, true, '2025-09-12 15:49:17', NULL, 2, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (307, 56, 'Financial Aid Documents', 'document', false, false, NULL, NULL, 9, '2025-09-25 17:27:25', '2025-09-25 17:27:25');
INSERT INTO public.application_checklist_items VALUES (201, 31, 'Personal Information', 'form', true, true, '2025-09-12 15:49:17', NULL, 1, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (202, 31, 'Educational Background', 'form', true, true, '2025-09-12 15:49:17', NULL, 2, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (204, 32, 'Personal Information', 'form', true, true, '2025-09-12 15:49:17', NULL, 1, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (205, 32, 'Educational Background', 'form', true, true, '2025-09-12 15:49:17', NULL, 2, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (207, 33, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (208, 33, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (210, 34, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (211, 34, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (213, 35, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (214, 35, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (216, 36, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (217, 36, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (219, 37, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (220, 37, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (222, 38, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (223, 38, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (225, 39, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (226, 39, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (228, 40, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (229, 40, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (231, 41, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (232, 41, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (234, 42, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (235, 42, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (237, 43, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (238, 43, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (240, 44, 'Personal Information', 'form', true, true, '2025-09-12 15:49:18', NULL, 1, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (241, 44, 'Educational Background', 'form', true, true, '2025-09-12 15:49:18', NULL, 2, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (179, 23, 'Application Fee', 'payment', true, false, NULL, NULL, 3, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (300, 56, 'documents', 'form', true, true, '2025-09-25 12:21:30', NULL, 0, '2025-09-24 22:55:47', '2025-09-25 12:21:30');
INSERT INTO public.application_checklist_items VALUES (182, 24, 'Application Fee', 'payment', true, false, NULL, NULL, 3, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (185, 25, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:17', NULL, 3, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (188, 26, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:17', NULL, 3, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (191, 27, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:17', NULL, 3, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (194, 28, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:17', NULL, 3, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (197, 29, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:17', NULL, 3, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (200, 30, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:17', NULL, 3, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (203, 31, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:17', NULL, 3, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_checklist_items VALUES (206, 32, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (209, 33, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (212, 34, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (215, 35, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (218, 36, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (221, 37, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (224, 38, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (227, 39, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (230, 40, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (233, 41, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (236, 42, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (239, 43, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (242, 44, 'Application Fee', 'payment', true, true, '2025-09-12 15:49:18', NULL, 3, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_checklist_items VALUES (296, 56, 'test-scores', 'form', true, true, '2025-09-25 11:51:54', NULL, 0, '2025-09-24 22:32:54', '2025-09-25 11:51:54');
INSERT INTO public.application_checklist_items VALUES (297, 56, 'essays', 'form', true, true, '2025-09-25 11:51:58', NULL, 0, '2025-09-24 22:37:15', '2025-09-25 11:51:58');
INSERT INTO public.application_checklist_items VALUES (299, 56, 'activities', 'form', true, true, '2025-09-25 11:52:02', NULL, 0, '2025-09-24 22:55:29', '2025-09-25 11:52:02');
INSERT INTO public.application_checklist_items VALUES (276, 55, 'Personal Information', 'form', true, false, NULL, NULL, 1, '2025-09-22 16:11:27', '2025-09-22 16:11:27');
INSERT INTO public.application_checklist_items VALUES (277, 55, 'Contact Information', 'form', true, false, NULL, NULL, 2, '2025-09-22 16:11:27', '2025-09-22 16:11:27');
INSERT INTO public.application_checklist_items VALUES (278, 55, 'Educational Background', 'form', true, false, NULL, NULL, 3, '2025-09-22 16:11:27', '2025-09-22 16:11:27');
INSERT INTO public.application_checklist_items VALUES (279, 55, 'Test Scores', 'test', false, false, NULL, NULL, 4, '2025-09-22 16:11:27', '2025-09-22 16:11:27');
INSERT INTO public.application_checklist_items VALUES (280, 55, 'Personal Essay', 'document', true, false, NULL, NULL, 5, '2025-09-22 16:11:27', '2025-09-22 16:11:27');
INSERT INTO public.application_checklist_items VALUES (281, 55, 'Transcripts', 'document', true, false, NULL, NULL, 6, '2025-09-22 16:11:27', '2025-09-22 16:11:27');
INSERT INTO public.application_checklist_items VALUES (282, 55, 'Recommendation Letters', 'document', false, false, NULL, NULL, 7, '2025-09-22 16:11:27', '2025-09-22 16:11:27');
INSERT INTO public.application_checklist_items VALUES (283, 55, 'Application Fee', 'other', true, false, NULL, NULL, 8, '2025-09-22 16:11:27', '2025-09-22 16:11:27');
INSERT INTO public.application_checklist_items VALUES (284, 55, 'Academic Information', 'form', true, true, '2025-09-23 00:24:08', NULL, 2, NULL, '2025-09-23 00:24:08');
INSERT INTO public.application_checklist_items VALUES (285, 55, 'Test Scores', 'form', true, true, '2025-09-23 02:05:58', NULL, 3, NULL, '2025-09-23 02:05:58');
INSERT INTO public.application_checklist_items VALUES (286, 55, 'Essays & Statements', 'form', true, true, '2025-09-23 02:10:45', NULL, 4, NULL, '2025-09-23 02:10:45');
INSERT INTO public.application_checklist_items VALUES (287, 56, 'Personal Information', 'form', true, false, NULL, NULL, 1, '2025-09-24 15:02:02', '2025-09-24 15:02:02');
INSERT INTO public.application_checklist_items VALUES (288, 56, 'Contact Information', 'form', true, false, NULL, NULL, 2, '2025-09-24 15:02:02', '2025-09-24 15:02:02');
INSERT INTO public.application_checklist_items VALUES (289, 56, 'Educational Background', 'form', true, false, NULL, NULL, 3, '2025-09-24 15:02:02', '2025-09-24 15:02:02');
INSERT INTO public.application_checklist_items VALUES (290, 56, 'Test Scores', 'test', false, false, NULL, NULL, 4, '2025-09-24 15:02:02', '2025-09-24 15:02:02');
INSERT INTO public.application_checklist_items VALUES (291, 56, 'Personal Essay', 'document', true, false, NULL, NULL, 5, '2025-09-24 15:02:02', '2025-09-24 15:02:02');
INSERT INTO public.application_checklist_items VALUES (292, 56, 'Transcripts', 'document', true, false, NULL, NULL, 6, '2025-09-24 15:02:02', '2025-09-24 15:02:02');
INSERT INTO public.application_checklist_items VALUES (293, 56, 'Recommendation Letters', 'document', false, false, NULL, NULL, 7, '2025-09-24 15:02:02', '2025-09-24 15:02:02');
INSERT INTO public.application_checklist_items VALUES (294, 56, 'Application Fee', 'other', true, false, NULL, NULL, 8, '2025-09-24 15:02:02', '2025-09-24 15:02:02');
INSERT INTO public.application_checklist_items VALUES (298, 56, 'academic', 'form', true, true, '2025-09-25 12:20:39', NULL, 0, '2025-09-24 22:53:59', '2025-09-25 12:20:39');
INSERT INTO public.application_checklist_items VALUES (295, 56, 'recommendations', 'form', true, true, '2025-09-25 12:20:45', NULL, 0, '2025-09-24 22:20:58', '2025-09-25 12:20:45');


--
-- Data for Name: application_communications; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: application_documents; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.application_documents VALUES (1, 30, 'transcript', 'Transcript', 'transcript_APP-2025-000008.pdf', 'documents/applications/30/transcript.pdf', 'application/pdf', 4506536, NULL, 'pending_verification', false, NULL, '2025-09-12 15:49:18', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (2, 30, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000008.pdf', 'documents/applications/30/personal_statement.pdf', 'application/pdf', 3735643, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (3, 30, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000008.pdf', 'documents/applications/30/recommendation_letter.pdf', 'application/pdf', 4491682, NULL, 'uploaded', true, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (4, 30, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000008.pdf', 'documents/applications/30/test_scores.pdf', 'application/pdf', 2374255, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (5, 30, 'passport', 'Passport', 'passport_APP-2025-000008.pdf', 'documents/applications/30/passport.pdf', 'application/pdf', 841984, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (6, 43, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000021.pdf', 'documents/applications/43/personal_statement.pdf', 'application/pdf', 1890057, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (7, 43, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000021.pdf', 'documents/applications/43/recommendation_letter.pdf', 'application/pdf', 477424, NULL, 'verified', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (8, 43, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000021.pdf', 'documents/applications/43/test_scores.pdf', 'application/pdf', 926145, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (9, 43, 'passport', 'Passport', 'passport_APP-2025-000021.pdf', 'documents/applications/43/passport.pdf', 'application/pdf', 4909078, NULL, 'verified', false, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (10, 25, 'transcript', 'Transcript', 'transcript_APP-2025-000003.pdf', 'documents/applications/25/transcript.pdf', 'application/pdf', 1208771, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (11, 25, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000003.pdf', 'documents/applications/25/personal_statement.pdf', 'application/pdf', 4173749, NULL, 'verified', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (12, 25, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000003.pdf', 'documents/applications/25/recommendation_letter.pdf', 'application/pdf', 2512577, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (13, 25, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000003.pdf', 'documents/applications/25/test_scores.pdf', 'application/pdf', 4270587, NULL, 'uploaded', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (14, 26, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000004.pdf', 'documents/applications/26/personal_statement.pdf', 'application/pdf', 2536737, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (15, 26, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000004.pdf', 'documents/applications/26/recommendation_letter.pdf', 'application/pdf', 4415279, NULL, 'verified', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (16, 26, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000004.pdf', 'documents/applications/26/test_scores.pdf', 'application/pdf', 3194698, NULL, 'pending_verification', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (17, 27, 'transcript', 'Transcript', 'transcript_APP-2025-000005.pdf', 'documents/applications/27/transcript.pdf', 'application/pdf', 3577049, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (18, 27, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000005.pdf', 'documents/applications/27/personal_statement.pdf', 'application/pdf', 2267577, NULL, 'pending_verification', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (19, 27, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000005.pdf', 'documents/applications/27/recommendation_letter.pdf', 'application/pdf', 4669682, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (20, 27, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000005.pdf', 'documents/applications/27/test_scores.pdf', 'application/pdf', 2482988, NULL, 'verified', false, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (21, 28, 'transcript', 'Transcript', 'transcript_APP-2025-000006.pdf', 'documents/applications/28/transcript.pdf', 'application/pdf', 847744, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (22, 28, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000006.pdf', 'documents/applications/28/personal_statement.pdf', 'application/pdf', 627442, NULL, 'pending_verification', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (23, 28, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000006.pdf', 'documents/applications/28/recommendation_letter.pdf', 'application/pdf', 2900783, NULL, 'pending_verification', false, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (24, 28, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000006.pdf', 'documents/applications/28/test_scores.pdf', 'application/pdf', 2118011, NULL, 'verified', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (25, 28, 'passport', 'Passport', 'passport_APP-2025-000006.pdf', 'documents/applications/28/passport.pdf', 'application/pdf', 2642020, NULL, 'verified', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (26, 29, 'transcript', 'Transcript', 'transcript_APP-2025-000007.pdf', 'documents/applications/29/transcript.pdf', 'application/pdf', 848825, NULL, 'verified', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (27, 29, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000007.pdf', 'documents/applications/29/personal_statement.pdf', 'application/pdf', 4863993, NULL, 'uploaded', false, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (28, 29, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000007.pdf', 'documents/applications/29/recommendation_letter.pdf', 'application/pdf', 2407407, NULL, 'pending_verification', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (29, 29, 'passport', 'Passport', 'passport_APP-2025-000007.pdf', 'documents/applications/29/passport.pdf', 'application/pdf', 2018683, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (30, 31, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000009.pdf', 'documents/applications/31/recommendation_letter.pdf', 'application/pdf', 2720817, NULL, 'uploaded', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (31, 31, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000009.pdf', 'documents/applications/31/test_scores.pdf', 'application/pdf', 2222961, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (32, 31, 'passport', 'Passport', 'passport_APP-2025-000009.pdf', 'documents/applications/31/passport.pdf', 'application/pdf', 643433, NULL, 'uploaded', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (33, 32, 'transcript', 'Transcript', 'transcript_APP-2025-000010.pdf', 'documents/applications/32/transcript.pdf', 'application/pdf', 2698121, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (34, 32, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000010.pdf', 'documents/applications/32/personal_statement.pdf', 'application/pdf', 2864105, NULL, 'pending_verification', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (35, 32, 'passport', 'Passport', 'passport_APP-2025-000010.pdf', 'documents/applications/32/passport.pdf', 'application/pdf', 1254605, NULL, 'uploaded', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (36, 33, 'transcript', 'Transcript', 'transcript_APP-2025-000011.pdf', 'documents/applications/33/transcript.pdf', 'application/pdf', 4424594, NULL, 'uploaded', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (37, 33, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000011.pdf', 'documents/applications/33/personal_statement.pdf', 'application/pdf', 4941035, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (38, 33, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000011.pdf', 'documents/applications/33/recommendation_letter.pdf', 'application/pdf', 2479175, NULL, 'uploaded', false, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (39, 33, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000011.pdf', 'documents/applications/33/test_scores.pdf', 'application/pdf', 3800414, NULL, 'uploaded', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (43, 55, 'transcript', 'Agile Process Diagram -1.png', NULL, 'applications/55/documents/1Og4YkJQfQdHBESMDrGN0R6yPFiXwMDDQ8tqMFWw.png', NULL, 214095, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 02:52:48', '2025-09-23 02:52:48', NULL, 'image/png', '2025-09-23 02:52:48');
INSERT INTO public.application_documents VALUES (44, 55, 'test_scores', 'Home Gym 4.jpg', NULL, 'applications/55/documents/XYWNuVKqNkgDxHUU0dE6U7m0qAcypeoW61A2drFA.jpg', NULL, 66970, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 02:54:06', '2025-09-23 02:54:06', NULL, 'image/jpeg', '2025-09-23 02:54:06');
INSERT INTO public.application_documents VALUES (45, 55, 'transcript', 'Average Issue Time.jpg', NULL, 'applications/55/documents/NrniLZs0KqpHb7eITgZ9NsCH2Gb7QKEHw9OnlAnY.jpg', NULL, 99407, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 02:58:52', '2025-09-23 02:58:52', NULL, 'image/jpeg', '2025-09-23 02:58:52');
INSERT INTO public.application_documents VALUES (46, 55, 'transcript', 'Average Issue Time.jpg', 'Average Issue Time.jpg', 'applications/55/documents/kHAfaLpy5IdFJIorsOfIiWMGGqtqouYq3hwZeLlw.jpg', 'jpg', 99407, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 03:43:27', '2025-09-23 03:43:27', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (47, 55, 'test_scores', 'Backyard Tree Stool.jpg', 'Backyard Tree Stool.jpg', 'applications/55/documents/M3KDrOYWTeNxVdqmCDkzNkCdVQWbXg1Yco23NvCQ.jpg', 'jpg', 178242, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 03:43:41', '2025-09-23 03:43:41', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (50, 55, 'transcript', 'unofficial_transcript_25000001_2025-09-07.pdf', 'unofficial_transcript_25000001_2025-09-07.pdf', 'applications/55/documents/qB0FaJvL19P56MMILHaeZHtCJWBaxmqHcvZiByVn.pdf', 'pdf', 2472, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 03:51:29', '2025-09-23 03:51:29', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (51, 55, 'test_scores', 'Iphone 16 Order Details.pdf', 'Iphone 16 Order Details.pdf', 'applications/55/documents/sITa1mMCg6lEVT1Eo4byNMORcmkgiVMkqZDs8SaU.pdf', 'pdf', 48931, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 03:52:13', '2025-09-23 03:52:13', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (52, 55, 'transcript', 'unofficial_transcript_25000001_2025-09-07.pdf', 'unofficial_transcript_25000001_2025-09-07.pdf', 'applications/55/documents/OG38lt0rFhZUheIH058xnlfEXAfdNYjpQ3MQKXzR.pdf', 'pdf', 2472, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 04:06:45', '2025-09-23 04:06:45', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (53, 55, 'test_scores', 'Agile Process Diagram -1.png', 'Agile Process Diagram -1.png', 'applications/55/documents/Bp3YFU9TgqFmHpdABBlDRtSKm7Am79tjuMJAP8qp.png', 'png', 214095, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 04:08:03', '2025-09-23 04:08:03', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (49, 55, 'resume', 'doc.png', 'doc.png', 'applications/55/documents/nXtFrK3QsTG0tHXHrE0HWeL2snAdqpMTYv5SnPH7.png', 'png', 100385, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 03:44:13', '2025-09-23 10:54:35', '2025-09-23 10:54:35', NULL, NULL);
INSERT INTO public.application_documents VALUES (54, 55, 'resume', 'doc (1).png', 'doc (1).png', 'applications/55/documents/JKt7OS699BzIDiu2a0ZyBpZPLhvKOzZZsFKFKupA.png', 'png', 14838, 'd7b12cb57994fff5f8999803f9c7f5f2', 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 10:54:35', '2025-09-23 10:54:35', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (55, 55, 'personal_statement', 'DWF.jpeg', 'DWF.jpeg', 'applications/55/documents/Ob3Bh7VirfEF2RIfewHflXV18vBAkZ2qxaDEkfQf.jpg', 'jpg', 398593, '612fd1062af32c91777c73c2301ad338', 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 11:04:21', '2025-09-23 11:04:21', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (48, 55, 'passport', 'Home Gym 2.jpg', 'Home Gym 2.jpg', 'applications/55/documents/K4VsIipuA4FVAU1T3aDhUBZulYnvHnQwcoPQ5r0n.jpg', 'jpg', 64524, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 03:43:55', '2025-09-23 11:05:04', '2025-09-23 11:05:04', NULL, NULL);
INSERT INTO public.application_documents VALUES (56, 55, 'passport', 'Native.png', 'Native.png', 'applications/55/documents/ZSxnMaYfhIOevdsnlQKPbBDyio4xsBrchhjeEIuh.png', 'png', 4396, 'ca50db835b04494bd3071b7df2af3f14', 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 11:05:04', '2025-09-23 11:05:04', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (64, 56, 'transcript', 'WASSCE Certificate', NULL, 'documents/applications/56/wassce_certificate.pdf', 'application/pdf', 1024000, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-25 17:27:25', '2025-09-25 17:27:25', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (65, 56, 'test_scores', 'WASSCE Results Statement', NULL, 'documents/applications/56/wassce_results.pdf', 'application/pdf', 512000, NULL, 'verified', true, 1, '2025-09-23 17:27:25', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-25 17:27:25', '2025-09-25 17:27:25', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (66, 56, 'personal_statement', 'Personal Statement', NULL, 'documents/applications/56/personal_statement.pdf', 'application/pdf', 256000, NULL, 'verified', true, 1, '2025-09-24 17:27:25', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-25 17:27:25', '2025-09-25 17:27:25', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (67, 56, 'recommendation_letter', 'Teacher Recommendation - Mathematics', NULL, 'documents/applications/56/rec_math.pdf', 'application/pdf', 256000, NULL, 'verified', true, 1, '2025-09-24 17:27:25', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-25 17:27:25', '2025-09-25 17:27:25', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (68, 56, 'recommendation_letter', 'Teacher Recommendation - Physics', NULL, 'documents/applications/56/rec_physics.pdf', 'application/pdf', 256000, NULL, 'pending_verification', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-25 17:27:25', '2025-09-25 17:27:25', NULL, NULL, NULL);
INSERT INTO public.application_documents VALUES (69, 56, 'birth_certificate', 'Birth Certificate', NULL, 'documents/applications/56/birth_cert.pdf', 'application/pdf', 128000, NULL, 'verified', true, 1, '2025-09-22 17:27:25', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-25 17:27:25', '2025-09-25 17:27:25', NULL, NULL, NULL);


--
-- Data for Name: application_documents_backup_20250915_133256; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.application_documents_backup_20250915_133256 VALUES (1, 30, 'transcript', 'Transcript', 'transcript_APP-2025-000008.pdf', 'documents/applications/30/transcript.pdf', 'application/pdf', 4506536, NULL, 'pending_verification', false, NULL, '2025-09-12 15:49:18', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (2, 30, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000008.pdf', 'documents/applications/30/personal_statement.pdf', 'application/pdf', 3735643, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (3, 30, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000008.pdf', 'documents/applications/30/recommendation_letter.pdf', 'application/pdf', 4491682, NULL, 'uploaded', true, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (4, 30, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000008.pdf', 'documents/applications/30/test_scores.pdf', 'application/pdf', 2374255, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (5, 30, 'passport', 'Passport', 'passport_APP-2025-000008.pdf', 'documents/applications/30/passport.pdf', 'application/pdf', 841984, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (6, 43, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000021.pdf', 'documents/applications/43/personal_statement.pdf', 'application/pdf', 1890057, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (7, 43, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000021.pdf', 'documents/applications/43/recommendation_letter.pdf', 'application/pdf', 477424, NULL, 'verified', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (8, 43, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000021.pdf', 'documents/applications/43/test_scores.pdf', 'application/pdf', 926145, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (9, 43, 'passport', 'Passport', 'passport_APP-2025-000021.pdf', 'documents/applications/43/passport.pdf', 'application/pdf', 4909078, NULL, 'verified', false, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (10, 25, 'transcript', 'Transcript', 'transcript_APP-2025-000003.pdf', 'documents/applications/25/transcript.pdf', 'application/pdf', 1208771, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (11, 25, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000003.pdf', 'documents/applications/25/personal_statement.pdf', 'application/pdf', 4173749, NULL, 'verified', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (12, 25, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000003.pdf', 'documents/applications/25/recommendation_letter.pdf', 'application/pdf', 2512577, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (13, 25, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000003.pdf', 'documents/applications/25/test_scores.pdf', 'application/pdf', 4270587, NULL, 'uploaded', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (14, 26, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000004.pdf', 'documents/applications/26/personal_statement.pdf', 'application/pdf', 2536737, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (15, 26, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000004.pdf', 'documents/applications/26/recommendation_letter.pdf', 'application/pdf', 4415279, NULL, 'verified', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (16, 26, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000004.pdf', 'documents/applications/26/test_scores.pdf', 'application/pdf', 3194698, NULL, 'pending_verification', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (17, 27, 'transcript', 'Transcript', 'transcript_APP-2025-000005.pdf', 'documents/applications/27/transcript.pdf', 'application/pdf', 3577049, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (18, 27, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000005.pdf', 'documents/applications/27/personal_statement.pdf', 'application/pdf', 2267577, NULL, 'pending_verification', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (19, 27, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000005.pdf', 'documents/applications/27/recommendation_letter.pdf', 'application/pdf', 4669682, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:19', '2025-09-12 15:49:19', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (20, 27, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000005.pdf', 'documents/applications/27/test_scores.pdf', 'application/pdf', 2482988, NULL, 'verified', false, NULL, '2025-09-12 15:49:19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (21, 28, 'transcript', 'Transcript', 'transcript_APP-2025-000006.pdf', 'documents/applications/28/transcript.pdf', 'application/pdf', 847744, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (22, 28, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000006.pdf', 'documents/applications/28/personal_statement.pdf', 'application/pdf', 627442, NULL, 'pending_verification', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (23, 28, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000006.pdf', 'documents/applications/28/recommendation_letter.pdf', 'application/pdf', 2900783, NULL, 'pending_verification', false, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (24, 28, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000006.pdf', 'documents/applications/28/test_scores.pdf', 'application/pdf', 2118011, NULL, 'verified', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (25, 28, 'passport', 'Passport', 'passport_APP-2025-000006.pdf', 'documents/applications/28/passport.pdf', 'application/pdf', 2642020, NULL, 'verified', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (26, 29, 'transcript', 'Transcript', 'transcript_APP-2025-000007.pdf', 'documents/applications/29/transcript.pdf', 'application/pdf', 848825, NULL, 'verified', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (27, 29, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000007.pdf', 'documents/applications/29/personal_statement.pdf', 'application/pdf', 4863993, NULL, 'uploaded', false, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (28, 29, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000007.pdf', 'documents/applications/29/recommendation_letter.pdf', 'application/pdf', 2407407, NULL, 'pending_verification', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (29, 29, 'passport', 'Passport', 'passport_APP-2025-000007.pdf', 'documents/applications/29/passport.pdf', 'application/pdf', 2018683, NULL, 'verified', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (30, 31, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000009.pdf', 'documents/applications/31/recommendation_letter.pdf', 'application/pdf', 2720817, NULL, 'uploaded', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (31, 31, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000009.pdf', 'documents/applications/31/test_scores.pdf', 'application/pdf', 2222961, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (32, 31, 'passport', 'Passport', 'passport_APP-2025-000009.pdf', 'documents/applications/31/passport.pdf', 'application/pdf', 643433, NULL, 'uploaded', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (33, 32, 'transcript', 'Transcript', 'transcript_APP-2025-000010.pdf', 'documents/applications/32/transcript.pdf', 'application/pdf', 2698121, NULL, 'uploaded', false, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (34, 32, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000010.pdf', 'documents/applications/32/personal_statement.pdf', 'application/pdf', 2864105, NULL, 'pending_verification', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (35, 32, 'passport', 'Passport', 'passport_APP-2025-000010.pdf', 'documents/applications/32/passport.pdf', 'application/pdf', 1254605, NULL, 'uploaded', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (36, 33, 'transcript', 'Transcript', 'transcript_APP-2025-000011.pdf', 'documents/applications/33/transcript.pdf', 'application/pdf', 4424594, NULL, 'uploaded', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (37, 33, 'personal_statement', 'Personal Statement', 'personal_statement_APP-2025-000011.pdf', 'documents/applications/33/personal_statement.pdf', 'application/pdf', 4941035, NULL, 'pending_verification', true, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (38, 33, 'recommendation_letter', 'Recommendation Letter', 'recommendation_letter_APP-2025-000011.pdf', 'documents/applications/33/recommendation_letter.pdf', 'application/pdf', 2479175, NULL, 'uploaded', false, NULL, '2025-09-12 15:49:20', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);
INSERT INTO public.application_documents_backup_20250915_133256 VALUES (39, 33, 'test_scores', 'Test Scores', 'test_scores_APP-2025-000011.pdf', 'documents/applications/33/test_scores.pdf', 'application/pdf', 3800414, NULL, 'uploaded', true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20', NULL);


--
-- Data for Name: application_fees; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.application_fees VALUES (21, 25, 'application_fee', 75.00, 'USD', 'paid', 'bank_transfer', 'TXN-97925686', NULL, NULL, '2025-08-13 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_fees VALUES (22, 26, 'application_fee', 75.00, 'USD', 'paid', 'bank_transfer', 'TXN-54670558', NULL, NULL, '2025-08-11 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_fees VALUES (23, 27, 'application_fee', 75.00, 'USD', 'paid', 'bank_transfer', 'TXN-46249065', NULL, NULL, '2025-08-10 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_fees VALUES (24, 28, 'application_fee', 75.00, 'USD', 'paid', 'bank_transfer', 'TXN-60814004', NULL, NULL, '2025-07-15 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_fees VALUES (25, 29, 'application_fee', 75.00, 'USD', 'paid', 'credit_card', 'TXN-05165161', NULL, NULL, '2025-09-07 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_fees VALUES (26, 30, 'application_fee', 75.00, 'USD', 'paid', 'credit_card', 'TXN-47089383', NULL, NULL, '2025-07-21 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_fees VALUES (27, 31, 'application_fee', 75.00, 'USD', 'paid', 'bank_transfer', 'TXN-30458843', NULL, NULL, '2025-08-07 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:17', '2025-09-12 15:49:17');
INSERT INTO public.application_fees VALUES (28, 32, 'application_fee', 75.00, 'USD', 'paid', 'credit_card', 'TXN-55287740', NULL, NULL, '2025-07-26 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (29, 33, 'application_fee', 75.00, 'USD', 'paid', 'mobile_money', 'TXN-04327086', NULL, NULL, '2025-07-15 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (30, 34, 'application_fee', 75.00, 'USD', 'paid', 'credit_card', 'TXN-18132618', NULL, NULL, '2025-07-13 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (31, 35, 'application_fee', 75.00, 'USD', 'paid', 'bank_transfer', 'TXN-18519827', NULL, NULL, '2025-07-12 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (32, 36, 'application_fee', 75.00, 'USD', 'paid', 'credit_card', 'TXN-53944743', NULL, NULL, '2025-07-22 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (33, 37, 'application_fee', 75.00, 'USD', 'paid', 'mobile_money', 'TXN-38033605', NULL, NULL, '2025-07-14 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (34, 38, 'application_fee', 75.00, 'USD', 'paid', 'mobile_money', 'TXN-01822983', NULL, NULL, '2025-08-02 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (35, 39, 'application_fee', 75.00, 'USD', 'paid', 'bank_transfer', 'TXN-95835973', NULL, NULL, '2025-08-07 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (36, 40, 'application_fee', 75.00, 'USD', 'paid', 'credit_card', 'TXN-77992094', NULL, NULL, '2025-07-22 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (37, 41, 'application_fee', 75.00, 'USD', 'paid', 'bank_transfer', 'TXN-39324742', NULL, NULL, '2025-08-04 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (38, 42, 'application_fee', 75.00, 'USD', 'paid', 'bank_transfer', 'TXN-07392773', NULL, NULL, '2025-09-10 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (39, 43, 'application_fee', 75.00, 'USD', 'paid', 'mobile_money', 'TXN-07339792', NULL, NULL, '2025-08-07 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');
INSERT INTO public.application_fees VALUES (40, 44, 'application_fee', 75.00, 'USD', 'paid', 'credit_card', 'TXN-97490024', NULL, NULL, '2025-09-06 00:00:00', NULL, NULL, NULL, '2025-09-12 15:49:18', '2025-09-12 15:49:18');


--
-- Data for Name: application_notes; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: application_reviews; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.application_reviews VALUES (1, 30, 161, 'academic_review', 2, 2, 2, 4, NULL, 2, 'Alias eligendi distinctio aut doloremque ut laborum.', NULL, NULL, 'Non aut id et perspiciatis recusandae earum.', 'Non velit repellendus aut sed voluptatem iure eos.', NULL, 'recommend_with_reservations', 'completed', '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20');
INSERT INTO public.application_reviews VALUES (2, 43, 162, 'committee_review', 1, 1, 2, 1, NULL, 2, 'Consequatur provident sit possimus.', NULL, NULL, 'Nemo pariatur quo reprehenderit repellendus esse sit voluptatem et.', 'Et magnam ut sunt modi.', NULL, 'recommend_with_reservations', 'completed', '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20');
INSERT INTO public.application_reviews VALUES (3, 31, 162, 'initial_review', 3, 5, 4, 2, NULL, 1, 'Molestias nihil consectetur eligendi quia in.', NULL, NULL, 'Sint nobis ab tempore quibusdam eligendi ut rerum.', 'Temporibus rerum et quaerat ipsum.', NULL, 'recommend', 'completed', '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20');
INSERT INTO public.application_reviews VALUES (4, 32, 162, 'committee_review', 1, 4, 3, 3, NULL, 5, 'Totam nihil ex ut animi rem modi praesentium.', NULL, NULL, 'Animi quis qui sed aperiam mollitia et consequatur.', 'Deleniti ut culpa enim.', NULL, 'recommend', 'completed', '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20');
INSERT INTO public.application_reviews VALUES (5, 35, 160, 'committee_review', 5, 4, 2, 1, NULL, 1, 'Omnis officia deserunt nihil enim rerum optio.', NULL, NULL, 'Omnis sunt saepe quis cumque.', 'Error eligendi quos eius similique.', NULL, 'recommend_with_reservations', 'completed', '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20');
INSERT INTO public.application_reviews VALUES (6, 36, 160, 'initial_review', 2, 4, 2, 5, NULL, 5, 'Eum ut corporis omnis rerum non qui eum.', NULL, NULL, 'Hic fugiat consequuntur incidunt.', 'Minima qui corporis non enim veritatis sunt nostrum.', NULL, 'strongly_recommend', 'completed', '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20');
INSERT INTO public.application_reviews VALUES (7, 44, 161, 'initial_review', 1, 1, 2, 4, NULL, 3, 'Aut ea animi ex amet saepe voluptate.', NULL, NULL, 'Et est quibusdam repellendus aut.', 'Placeat id minima doloribus nisi assumenda eos autem vitae.', NULL, 'recommend_with_reservations', 'completed', '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20');
INSERT INTO public.application_reviews VALUES (8, 38, 160, 'committee_review', 5, 5, 2, 3, NULL, 5, 'Aut in temporibus voluptatem qui.', NULL, NULL, 'Minima iusto quos quibusdam sed.', 'Totam rerum et occaecati dolore quo.', NULL, 'recommend_with_reservations', 'completed', '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20');
INSERT INTO public.application_reviews VALUES (9, 39, 162, 'academic_review', 1, 3, 5, 5, NULL, 1, 'Consequatur non sapiente molestias nostrum alias sit aut at.', NULL, NULL, 'Mollitia possimus voluptatum ut sed nihil quibusdam exercitationem laborum.', 'Voluptatem ea non repellat cumque omnis laudantium ipsa et.', NULL, 'recommend_with_reservations', 'completed', '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20');
INSERT INTO public.application_reviews VALUES (10, 40, 162, 'initial_review', 5, 2, 1, 2, NULL, 1, 'Fugiat tempora ratione alias nostrum ipsam voluptas totam quae.', NULL, NULL, 'Ducimus autem maxime quidem nobis illo ut unde accusantium.', 'Maiores dignissimos officia ut.', NULL, 'strongly_recommend', 'completed', '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', NULL, '2025-09-12 15:49:20', '2025-09-12 15:49:20');


--
-- Data for Name: application_statistics; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: application_status_histories; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: application_status_history; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: application_templates; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: course_sites; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.course_sites VALUES (1, 1, 'CS101-01-2025-FALL', 'Introduction to Computer Science (Fall 2025)', 'This course covers the fundamental concepts of Introduction to Computer Science.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (2, 2, 'CS102-01-2025-FALL', 'Programming Fundamentals (Fall 2025)', 'This course covers the fundamental concepts of Programming Fundamentals.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (3, 3, 'CS201-01-2025-FALL', 'Data Structures (Fall 2025)', 'This course covers the fundamental concepts of Data Structures.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (4, 4, 'CS202-01-2025-FALL', 'Algorithms (Fall 2025)', 'This course covers the fundamental concepts of Algorithms.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (5, 5, 'CS301-01-2025-FALL', 'Database Systems (Fall 2025)', 'This course covers the fundamental concepts of Database Systems.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (6, 6, 'CS302-01-2025-FALL', 'Software Engineering (Fall 2025)', 'This course covers the fundamental concepts of Software Engineering.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (7, 7, 'CS303-01-2025-FALL', 'Web Development (Fall 2025)', 'This course covers the fundamental concepts of Web Development.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (8, 8, 'CS401-01-2025-FALL', 'Artificial Intelligence (Fall 2025)', 'This course covers the fundamental concepts of Artificial Intelligence.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (9, 9, 'BUS101-01-2025-FALL', 'Introduction to Business (Fall 2025)', 'This course covers the fundamental concepts of Introduction to Business.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (10, 10, 'BUS102-01-2025-FALL', 'Principles of Management (Fall 2025)', 'This course covers the fundamental concepts of Principles of Management.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (11, 11, 'ACC101-01-2025-FALL', 'Financial Accounting (Fall 2025)', 'This course covers the fundamental concepts of Financial Accounting.', NULL, true, false, '[]', NULL, '2025-09-06 17:58:51', '2025-09-06 17:58:51');
INSERT INTO public.course_sites VALUES (12, 13, 'MKT201-01-2025-FALL', 'Marketing Principles (Fall 2025)', 'This course covers the fundamental concepts of Marketing Principles.', NULL, true, false, '[]', NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13');
INSERT INTO public.course_sites VALUES (13, 14, 'FIN201-01-2025-FALL', 'Corporate Finance (Fall 2025)', 'This course covers the fundamental concepts of Corporate Finance.', NULL, true, false, '[]', NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13');
INSERT INTO public.course_sites VALUES (14, 15, 'BUS301-01-2025-FALL', 'Business Ethics (Fall 2025)', 'This course covers the fundamental concepts of Business Ethics.', NULL, true, false, '[]', NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13');
INSERT INTO public.course_sites VALUES (15, 16, 'ENG101-01-2025-FALL', 'English Composition I (Fall 2025)', 'This course covers the fundamental concepts of English Composition I.', NULL, true, false, '[]', NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13');
INSERT INTO public.course_sites VALUES (16, 17, 'ENG102-01-2025-FALL', 'English Composition II (Fall 2025)', 'This course covers the fundamental concepts of English Composition II.', NULL, true, false, '[]', NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13');
INSERT INTO public.course_sites VALUES (17, 18, 'MATH101-01-2025-FALL', 'Calculus I (Fall 2025)', 'This course covers the fundamental concepts of Calculus I.', NULL, true, false, '[]', NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13');
INSERT INTO public.course_sites VALUES (18, 19, 'MATH102-01-2025-FALL', 'Calculus II (Fall 2025)', 'This course covers the fundamental concepts of Calculus II.', NULL, true, false, '[]', NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13');
INSERT INTO public.course_sites VALUES (19, 20, 'PHY101-01-2025-FALL', 'Physics I (Fall 2025)', 'This course covers the fundamental concepts of Physics I.', NULL, true, false, '[]', NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13');
INSERT INTO public.course_sites VALUES (20, 21, 'CHEM101-01-2025-FALL', 'Chemistry I (Fall 2025)', 'This course covers the fundamental concepts of Chemistry I.', NULL, true, false, '[]', NULL, '2025-09-06 18:02:13', '2025-09-06 18:02:13');


--
-- Data for Name: assignments; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: assignment_groups; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: assignment_group_members; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: assignment_submissions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: attendance; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: attendance_alerts; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: attendance_configurations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.attendance_configurations VALUES (1, true, 3, 10.00, 'percentage', NULL, true, 3, '2025-09-07 09:58:49', '2025-09-07 09:58:49');


--
-- Data for Name: attendance_excuses; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: attendance_policies; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: attendance_sessions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: attendance_records; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: attendance_statistics; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: fee_structures; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.fee_structures VALUES (1, 'Undergraduate Tuition', 'TUITION_UG', 'Standard Undergraduate Tuition for eligible students', 'tuition', 'per_credit', 475.00, 'undergraduate', NULL, true, true, '2025-01-01', NULL, '2025-09-02 21:39:00', '2025-09-03 16:59:41', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (2, 'Graduate Tuition', 'TUITION_GR', 'Standard Graduate Tuition for eligible students', 'tuition', 'per_credit', 675.00, 'graduate', NULL, true, true, '2025-01-01', NULL, '2025-09-02 21:39:00', '2025-09-03 16:59:41', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (3, 'Registration Fee', 'REG_FEE', 'Standard Registration Fee for eligible students', 'registration', 'per_term', 150.00, NULL, NULL, true, true, '2025-01-01', NULL, '2025-09-02 21:39:00', '2025-09-03 16:59:41', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (4, 'Technology Fee', 'TECH_FEE', 'Standard Technology Fee for eligible students', 'technology', 'per_term', 125.00, NULL, NULL, true, true, '2025-01-01', NULL, '2025-09-02 21:39:00', '2025-09-03 16:59:41', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (5, 'Library Fee', 'LIB_FEE', 'Standard Library Fee for eligible students', 'library', 'per_term', 75.00, NULL, NULL, true, true, '2025-01-01', NULL, '2025-09-02 21:39:00', '2025-09-03 16:59:41', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (6, 'Student Activity Fee', 'ACT_FEE', 'Standard Student Activity Fee for eligible students', 'activity', 'per_term', 100.00, NULL, NULL, true, true, '2025-01-01', NULL, '2025-09-02 21:39:00', '2025-09-03 16:59:41', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (7, 'Health Services Fee', 'HEALTH_FEE', 'Standard Health Services Fee for eligible students', 'health', 'per_term', 250.00, NULL, NULL, true, true, '2025-01-01', NULL, '2025-09-02 21:39:00', '2025-09-03 16:59:41', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (8, 'Lab Fee - Science', 'LAB_SCI', 'Standard Lab Fee - Science for eligible students', 'lab', 'per_term', 175.00, NULL, NULL, false, true, '2025-01-01', NULL, '2025-09-02 21:39:00', '2025-09-03 17:07:25', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (9, 'Lab Fee - Computer', 'LAB_COMP', 'Standard Lab Fee - Computer for eligible students', 'lab', 'per_term', 100.00, NULL, NULL, false, true, '2025-01-01', NULL, '2025-09-02 21:39:00', '2025-09-03 17:07:25', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (10, 'Parking Permit', 'PARKING', 'Standard Parking Permit for eligible students', 'other', 'per_term', 200.00, NULL, NULL, false, true, '2025-01-01', NULL, '2025-09-03 17:07:25', '2025-09-03 17:07:25', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (11, 'Late Registration Fee', 'LATE_REG', 'Standard Late Registration Fee for eligible students', 'other', 'once', 50.00, NULL, NULL, false, true, '2025-01-01', NULL, '2025-09-03 17:07:25', '2025-09-03 17:07:25', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (12, 'International Student Fee', 'INTL_FEE', 'Standard International Student Fee for eligible students', 'other', 'per_term', 350.00, NULL, NULL, false, true, '2025-01-01', NULL, '2025-09-03 17:07:25', '2025-09-03 17:07:25', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.fee_structures VALUES (13, 'Online Course Fee', 'ONLINE_FEE', 'Standard Online Course Fee for eligible students', 'other', 'per_credit', 50.00, NULL, NULL, false, true, '2025-01-01', NULL, '2025-09-03 17:07:25', '2025-09-03 17:07:25', NULL, NULL, NULL, NULL, NULL, NULL);


--
-- Data for Name: billing_items; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: buildings; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.buildings VALUES (1, 'MAIN', 'Main Building', NULL, 4, NULL, NULL, NULL, true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.buildings VALUES (2, 'SCI', 'Science Block', NULL, 3, NULL, NULL, NULL, true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.buildings VALUES (3, 'ENG', 'Engineering Building', NULL, 5, NULL, NULL, NULL, true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.buildings VALUES (4, 'LIB', 'Library Complex', NULL, 2, NULL, NULL, NULL, true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.cache VALUES ('intellicampus-cache-admin_app_stats_96005b38eeffba944e4e6e2909d474c9', 'a:8:{s:5:"total";i:25;s:9:"submitted";i:6;s:12:"under_review";i:3;s:8:"admitted";i:0;s:6:"denied";i:0;s:16:"pending_decision";i:21;s:5:"today";i:0;s:9:"this_week";i:2;}', 1758836462);
INSERT INTO public.cache VALUES ('intellicampus-cache-admission_portal_stats', 'a:4:{s:18:"total_applications";i:20;s:18:"programs_available";i:5;s:8:"deadline";O:25:"Illuminate\Support\Carbon":3:{s:4:"date";s:26:"2025-11-14 00:00:00.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";}s:15:"spots_available";i:500;}', 1758843691);
INSERT INTO public.cache VALUES ('intellicampus-cache-admission_stats_5677d8abb08a3d4d41c83866ce620862', 'a:7:{s:7:"summary";a:9:{s:18:"total_applications";i:0;s:22:"completed_applications";i:0;s:11:"in_progress";i:0;s:8:"reviewed";i:0;s:14:"pending_review";i:0;s:8:"admitted";i:0;s:6:"denied";i:0;s:10:"waitlisted";i:0;s:8:"enrolled";i:0;}s:9:"by_status";a:0:{}s:7:"by_type";a:0:{}s:10:"by_program";a:0:{}s:9:"by_source";a:4:{s:7:"website";i:45;s:8:"referral";i:25;s:12:"social_media";i:20;s:5:"other";i:10;}s:8:"timeline";a:0:{}s:15:"quality_metrics";a:5:{s:11:"average_gpa";N;s:10:"median_gpa";N;s:9:"gpa_range";a:2:{s:3:"min";N;s:3:"max";N;}s:11:"test_scores";a:0:{}s:15:"completion_rate";i:0;}}', 1758885882);


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: calendar_events; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: countries; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: states; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: cities; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: rooms; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.rooms VALUES (1, 1, 'MAIN-CR01', 'Classroom 1', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (2, 1, 'MAIN-CR02', 'Classroom 2', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (3, 1, 'MAIN-CR03', 'Classroom 3', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (4, 1, 'MAIN-CR04', 'Classroom 4', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (5, 1, 'MAIN-CR05', 'Classroom 5', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (6, 1, 'MAIN-LAB01', 'Lab 1', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (7, 1, 'MAIN-LAB02', 'Lab 2', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (8, 1, 'MAIN-LAB03', 'Lab 3', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (9, 1, 'MAIN-SR01', 'Seminar 1', 'seminar', 20, 12, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (10, 1, 'MAIN-SR02', 'Seminar 2', 'seminar', 20, 12, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (11, 2, 'SCI-CR01', 'Classroom 1', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (12, 2, 'SCI-CR02', 'Classroom 2', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (13, 2, 'SCI-CR03', 'Classroom 3', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (14, 2, 'SCI-CR04', 'Classroom 4', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (15, 2, 'SCI-CR05', 'Classroom 5', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (16, 2, 'SCI-LAB01', 'Lab 1', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (17, 2, 'SCI-LAB02', 'Lab 2', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (18, 2, 'SCI-LAB03', 'Lab 3', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (19, 2, 'SCI-SR01', 'Seminar 1', 'seminar', 20, 12, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (20, 2, 'SCI-SR02', 'Seminar 2', 'seminar', 20, 12, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (21, 3, 'ENG-CR01', 'Classroom 1', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (22, 3, 'ENG-CR02', 'Classroom 2', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (23, 3, 'ENG-CR03', 'Classroom 3', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (24, 3, 'ENG-CR04', 'Classroom 4', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (25, 3, 'ENG-CR05', 'Classroom 5', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (26, 3, 'ENG-LAB01', 'Lab 1', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (27, 3, 'ENG-LAB02', 'Lab 2', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (28, 3, 'ENG-LAB03', 'Lab 3', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (29, 3, 'ENG-SR01', 'Seminar 1', 'seminar', 20, 12, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (30, 3, 'ENG-SR02', 'Seminar 2', 'seminar', 20, 12, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (31, 4, 'LIB-CR01', 'Classroom 1', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (32, 4, 'LIB-CR02', 'Classroom 2', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (33, 4, 'LIB-CR03', 'Classroom 3', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (34, 4, 'LIB-CR04', 'Classroom 4', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (35, 4, 'LIB-CR05', 'Classroom 5', 'classroom', 40, 24, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (36, 4, 'LIB-LAB01', 'Lab 1', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (37, 4, 'LIB-LAB02', 'Lab 2', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (38, 4, 'LIB-LAB03', 'Lab 3', 'lab', 30, 18, '["computers","projector"]', NULL, false, false, true, true, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (39, 4, 'LIB-SR01', 'Seminar 1', 'seminar', 20, 12, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.rooms VALUES (40, 4, 'LIB-SR02', 'Seminar 2', 'seminar', 20, 12, '["projector","whiteboard"]', NULL, false, false, true, false, true, NULL, '2025-09-07 10:00:03', '2025-09-07 10:00:03');


--
-- Data for Name: class_schedules; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: student_accounts; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.student_accounts VALUES (2, 3, 12072.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:00', '2025-09-03 22:47:42', 'ACC2025000003', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (64, 63, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000063', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (26, 20, 5216.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:46', 'ACC2025000020', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (66, 65, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000065', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (3, 4, 13298.00, 11325.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:00', '2025-09-03 22:47:43', 'ACC2025000004', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (70, 69, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000069', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (71, 70, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000070', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (72, 71, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000071', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (74, 73, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000073', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (43, 39, 7449.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:48', 'ACC2025000039', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (47, 44, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000044', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (5, 7, 8434.00, 8175.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:43', 'ACC2025000007', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (36, 31, 4319.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:48', 'ACC2025000031', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (49, 46, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000046', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (50, 47, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000047', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (51, 48, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000048', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (6, 2, 17800.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:43', 'ACC2025000002', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (8, 17, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-02 21:39:01', 'ACC2025000017', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (10, 41, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-02 21:39:01', 'ACC2025000041', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (11, 49, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-02 21:39:01', 'ACC2025000049', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (14, 83, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-02 21:39:01', 'ACC2025000083', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (54, 52, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000052', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (38, 33, 13045.00, 11325.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:48', 'ACC2025000033', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (55, 53, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000053', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (56, 55, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000055', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (24, 18, 12228.00, 10275.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:46', 'ACC2025000018', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (57, 56, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000056', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (60, 59, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000059', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (61, 60, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000060', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (39, 34, 10983.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:48', 'ACC2025000034', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (25, 19, 11574.00, 11325.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:46', 'ACC2025000019', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (40, 36, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000036', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (62, 61, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000061', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (78, 77, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000077', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (4, 5, 3043.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:43', 'ACC2025000005', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (1, 11, 5650.00, 5700.00, 2000.00, 0.00, 500.00, 'active', false, '2025-09-02', NULL, '2025-09-02 21:39:00', '2025-09-03 22:47:42', 'ACC2025000011', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (7, 6, 6285.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:43', 'ACC2025000006', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (9, 24, 10739.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:44', 'ACC2025000024', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (12, 54, 2435.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:44', 'ACC2025000054', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (13, 35, 8472.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:44', 'ACC2025000035', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (15, 92, 6400.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:44', 'ACC2025000092', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (16, 103, 3141.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:44', 'ACC2025000103', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (17, 1, 8967.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:45', 'ACC2025000001', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (18, 13, 10250.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:45', 'ACC2025000013', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (19, 8, 3902.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:45', 'ACC2025000008', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (20, 9, 6433.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:45', 'ACC2025000009', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (21, 12, 6477.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:45', 'ACC2025000012', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (22, 15, 1543.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:45', 'ACC2025000015', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (23, 16, 11367.00, 10275.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:46', 'ACC2025000016', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (27, 21, 13486.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:46', 'ACC2025000021', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (28, 22, 7858.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:46', 'ACC2025000022', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (29, 14, 5696.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:47', 'ACC2025000014', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (30, 25, 2845.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:47', 'ACC2025000025', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (31, 26, 9950.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:47', 'ACC2025000026', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (32, 27, 12300.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:47', 'ACC2025000027', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (33, 28, 13725.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:01', '2025-09-03 22:47:47', 'ACC2025000028', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (34, 29, 13910.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:47', 'ACC2025000029', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (35, 30, 12218.00, 11325.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:47', 'ACC2025000030', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (37, 32, 9231.00, 9225.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:48', 'ACC2025000032', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (41, 37, 10705.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:48', 'ACC2025000037', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (42, 38, 14950.00, 11325.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:48', 'ACC2025000038', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (44, 40, 9450.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:49', 'ACC2025000040', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (45, 42, 9747.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:49', 'ACC2025000042', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (46, 43, 12100.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:49', 'ACC2025000043', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (48, 45, 10554.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:49', 'ACC2025000045', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (52, 50, 6746.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:49', 'ACC2025000050', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (53, 51, 4506.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:49', 'ACC2025000051', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (58, 57, 8025.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:49', 'ACC2025000057', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (59, 58, 4026.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:50', 'ACC2025000058', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (63, 62, 6768.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:50', 'ACC2025000062', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (65, 64, 4648.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:50', 'ACC2025000064', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (67, 66, 7049.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:50', 'ACC2025000066', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (68, 67, 7825.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:50', 'ACC2025000067', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (69, 68, 5998.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:50', 'ACC2025000068', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (73, 72, 7562.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:51', 'ACC2025000072', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (76, 75, 7825.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:51', 'ACC2025000075', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (77, 76, 7411.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:51', 'ACC2025000076', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (79, 78, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000078', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (82, 81, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000081', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (83, 82, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-02 21:39:02', 'ACC2025000082', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (87, 87, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-02 21:39:03', 'ACC2025000087', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (89, 89, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-02 21:39:03', 'ACC2025000089', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (90, 90, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-02 21:39:03', 'ACC2025000090', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (91, 91, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-02 21:39:03', 'ACC2025000091', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (92, 93, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-02 21:39:03', 'ACC2025000093', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (93, 94, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-02 21:39:03', 'ACC2025000094', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (99, 100, 0.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-02 21:39:03', 'ACC2025000100', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (75, 74, 9450.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:51', 'ACC2025000074', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (80, 79, 5733.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:51', 'ACC2025000079', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (81, 80, 6793.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:51', 'ACC2025000080', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (84, 84, 5776.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:02', '2025-09-03 22:47:52', 'ACC2025000084', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (85, 85, 9250.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-03 22:47:52', 'ACC2025000085', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (86, 86, 6230.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-03 22:47:52', 'ACC2025000086', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (88, 88, 2830.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-03 22:47:52', 'ACC2025000088', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (94, 95, 5482.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-03 22:47:52', 'ACC2025000095', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (95, 96, 9250.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-03 22:47:52', 'ACC2025000096', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (96, 97, 2463.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-03 22:47:53', 'ACC2025000097', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (97, 98, 2627.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-03 22:47:53', 'ACC2025000098', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (98, 99, 9450.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-03 22:47:53', 'ACC2025000099', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (100, 10, 13525.00, 10275.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-03 22:47:53', 'ACC2025000010', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (101, 23, 6598.00, 0.00, 0.00, 0.00, 500.00, 'active', false, NULL, NULL, '2025-09-02 21:39:03', '2025-09-03 22:47:53', 'ACC2025000023', NULL, NULL, NULL, false, NULL);
INSERT INTO public.student_accounts VALUES (102, 105, 0.00, 0.00, 0.00, 0.00, 0.00, 'active', false, NULL, NULL, '2025-09-06 01:09:22', '2025-09-06 01:09:22', 'ACC2025000105', NULL, NULL, NULL, false, NULL);


--
-- Data for Name: collection_accounts; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: content_folders; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: content_items; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: content_access_logs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: content_items_backup_20250915_133256; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: course_prerequisites; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: requirement_categories; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.requirement_categories VALUES (13, 'GEN_ED', 'General Education', 'University-wide general education requirements', 'general_education', 1, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.requirement_categories VALUES (14, 'MAJOR_CORE', 'Major Core Requirements', 'Core courses required for the major', 'major', 2, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.requirement_categories VALUES (15, 'MAJOR_ELEC', 'Major Electives', 'Elective courses within the major', 'major', 3, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.requirement_categories VALUES (16, 'MINOR', 'Minor Requirements', 'Requirements for minor programs', 'minor', 4, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.requirement_categories VALUES (17, 'FREE_ELEC', 'Free Electives', 'Open elective credits', 'elective', 5, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.requirement_categories VALUES (18, 'UNIVERSITY', 'University Requirements', 'University-wide requirements', 'university', 6, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');


--
-- Data for Name: degree_requirements; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.degree_requirements VALUES (17, 13, 'GE_ENGLISH', 'English Composition', 'English composition and writing skills', 'specific_courses', '"{\"required_courses\":[\"ENG101\",\"ENG102\"],\"min_grade\":\"C\"}"', 1, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (18, 13, 'GE_MATH', 'Mathematics', 'College-level mathematics', 'course_list', '"{\"choose_from\":[\"MATH151\",\"MATH161\",\"MATH171\"],\"min_to_choose\":1,\"min_grade\":\"C\"}"', 2, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (19, 13, 'GE_SCIENCE', 'Natural Sciences', 'Natural science courses with lab', 'credit_hours', '"{\"min_credits\":8,\"min_courses\":2,\"must_include_lab\":true,\"min_grade\":\"D\"}"', 3, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (20, 13, 'GE_HUMANITIES', 'Humanities', 'Humanities and arts courses', 'credit_hours', '"{\"min_credits\":9,\"min_courses\":3,\"min_grade\":\"D\"}"', 4, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (21, 13, 'GE_SOCIAL', 'Social Sciences', 'Social science courses', 'credit_hours', '"{\"min_credits\":9,\"min_courses\":3,\"min_grade\":\"D\"}"', 5, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (22, 14, 'CS_INTRO', 'Introduction to Computer Science', 'Introductory CS sequence', 'specific_courses', '"{\"required_courses\":[\"CS101\",\"CS102\"],\"min_grade\":\"C\"}"', 1, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (23, 14, 'CS_PROGRAMMING', 'Programming Fundamentals', 'Core programming courses', 'specific_courses', '"{\"required_courses\":[\"CS201\",\"CS202\",\"CS203\"],\"min_grade\":\"C\"}"', 2, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (24, 14, 'CS_SYSTEMS', 'Computer Systems', 'Computer architecture and operating systems', 'specific_courses', '"{\"required_courses\":[\"CS301\",\"CS302\"],\"min_grade\":\"C\"}"', 3, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (25, 14, 'CS_THEORY', 'Theoretical Computer Science', 'Algorithms and theory courses', 'specific_courses', '"{\"required_courses\":[\"CS311\",\"CS312\"],\"min_grade\":\"C\"}"', 4, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (26, 14, 'CS_CAPSTONE', 'Senior Capstone', 'Senior capstone project', 'specific_courses', '"{\"required_courses\":[\"CS490\"],\"min_grade\":\"C\"}"', 5, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (27, 15, 'CS_ELECTIVES', 'Computer Science Electives', 'Upper-level CS elective courses', 'credit_hours', '"{\"min_credits\":12,\"min_courses\":4,\"course_level_min\":300,\"min_grade\":\"C\"}"', 1, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (28, 17, 'FREE_ELECTIVES', 'Free Electives', 'Additional elective credits', 'credit_hours', '"{\"min_credits\":15,\"allow_pass_fail\":true}"', 1, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (29, 18, 'TOTAL_CREDITS', 'Total Credit Hours', 'Minimum total credits for graduation', 'credit_hours', '"{\"min_credits\":120}"', 1, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (30, 18, 'GPA_CUMULATIVE', 'Cumulative GPA', 'Minimum cumulative GPA', 'gpa', '"{\"min_gpa\":2}"', 2, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (31, 18, 'GPA_MAJOR', 'Major GPA', 'Minimum GPA in major courses', 'gpa', '"{\"min_gpa\":2,\"apply_to\":\"major\"}"', 3, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.degree_requirements VALUES (32, 18, 'RESIDENCY', 'Residency Requirement', 'Minimum credits at this institution', 'residency', '"{\"min_credits\":30,\"of_last_credits\":60}"', 4, true, true, NULL, NULL, '2025-09-09 12:18:20', '2025-09-09 12:18:20');


--
-- Data for Name: course_requirement_mappings; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.course_requirement_mappings VALUES (1, 16, 17, 'full', NULL, 'C', NULL, NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.course_requirement_mappings VALUES (2, 17, 17, 'full', NULL, 'C', NULL, NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.course_requirement_mappings VALUES (3, 1, 22, 'full', NULL, 'C', NULL, NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.course_requirement_mappings VALUES (4, 3, 22, 'full', NULL, 'C', NULL, NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.course_requirement_mappings VALUES (5, 4, 23, 'full', NULL, 'C', NULL, NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.course_requirement_mappings VALUES (6, 5, 23, 'full', NULL, 'C', NULL, NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.course_requirement_mappings VALUES (7, 6, 24, 'full', NULL, 'C', NULL, NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.course_requirement_mappings VALUES (8, 7, 24, 'full', NULL, 'C', NULL, NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');


--
-- Data for Name: credit_configurations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.credit_configurations VALUES (1, 'semester_hours', 12, 18, 21, 120, 1.00, NULL, '2025-09-07 09:58:49', '2025-09-07 09:58:49');


--
-- Data for Name: credit_overload_permissions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: deans_list; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: degree_audit_reports; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.degree_audit_reports VALUES (1, 11, 1, 1, 'unofficial', '2025-2026', 173.0, 186.0, 0.0, 89.0, 100.00, 3.66, 3.66, 0.00, false, 6, '2027-09-09', '{"13":{"category_id":13,"category_name":"General Education","category_type":"general_education","requirements":[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":26,"total_completed":93,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":100},"14":{"category_id":14,"category_name":"Major Core Requirements","category_type":"major","requirements":[{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":true,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":2,"courses_remaining":0,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":["CS101","CS102"],"remaining_courses":[]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":1,"courses_remaining":2,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":["CS201"],"remaining_courses":["CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]}],"total_required":0,"total_completed":0,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":0},"15":{"category_id":15,"category_name":"Major Electives","category_type":"major","requirements":[{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":12,"total_completed":31,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"17":{"category_id":17,"category_name":"Free Electives","category_type":"elective","requirements":[{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":15,"total_completed":31,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"18":{"category_id":18,"category_name":"University Requirements","category_type":"university","requirements":[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":25.833333333333336,"credits_required":120,"credits_completed":31,"credits_in_progress":0,"credits_remaining":89,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.66","gpa_type":"cumulative"},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":31,"of_last_credits":60}],"total_required":120,"total_completed":31,"total_in_progress":0,"total_remaining":89,"is_satisfied":false,"completion_percentage":25.833333333333336}}', '[{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":true,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":2,"courses_remaining":0,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":["CS101","CS102"],"remaining_courses":[]},{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.66","gpa_type":"cumulative"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":31,"of_last_credits":60}]', '[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":25.833333333333336,"credits_required":120,"credits_completed":31,"credits_in_progress":0,"credits_remaining":89,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}]', '[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":1,"courses_remaining":2,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":["CS201"],"remaining_courses":["CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"}]', '[{"type":"credits","priority":"medium","message":"Need 89 credits in Total Credit Hours","requirement":"Total Credit Hours"}]', 1, '2025-09-09 15:25:59', false, '2025-09-09 15:25:59', '2025-09-09 15:25:59');
INSERT INTO public.degree_audit_reports VALUES (2, 11, 1, 1, 'unofficial', '2025-2026', 120.0, 0.0, 10.0, 120.0, 0.00, 3.66, 3.66, 0.00, false, 8, '2028-05-09', '{"13":{"category_id":13,"category_name":"General Education","category_type":"general_education","requirements":[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":26,"total_completed":93,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":100},"14":{"category_id":14,"category_name":"Major Core Requirements","category_type":"major","requirements":[{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":true,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":2,"courses_remaining":0,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":["CS101","CS102"],"remaining_courses":[]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":1,"courses_remaining":2,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":["CS201"],"remaining_courses":["CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]}],"total_required":0,"total_completed":0,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":0},"15":{"category_id":15,"category_name":"Major Electives","category_type":"major","requirements":[{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":12,"total_completed":31,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"17":{"category_id":17,"category_name":"Free Electives","category_type":"elective","requirements":[{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":15,"total_completed":31,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"18":{"category_id":18,"category_name":"University Requirements","category_type":"university","requirements":[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":25.833333333333336,"credits_required":120,"credits_completed":31,"credits_in_progress":0,"credits_remaining":89,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.66","gpa_type":"cumulative"},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":31,"of_last_credits":60}],"total_required":120,"total_completed":31,"total_in_progress":0,"total_remaining":89,"is_satisfied":false,"completion_percentage":25.833333333333336}}', '[{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":true,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":2,"courses_remaining":0,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":["CS101","CS102"],"remaining_courses":[]},{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":31,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.66","gpa_type":"cumulative"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":31,"of_last_credits":60}]', '[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":25.833333333333336,"credits_required":120,"credits_completed":31,"credits_in_progress":0,"credits_remaining":89,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}]', '[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":1,"courses_remaining":2,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":["CS201"],"remaining_courses":["CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"}]', '[{"type":"credits","priority":"medium","message":"Need 89 credits in Total Credit Hours","requirement":"Total Credit Hours"}]', 1, '2025-09-09 15:56:03', false, '2025-09-09 15:56:03', '2025-09-09 15:56:03');
INSERT INTO public.degree_audit_reports VALUES (3, 94, 1, 1, 'unofficial', '2025-2026', 120.0, 0.0, 0.0, 120.0, 0.00, 3.83, 3.83, 0.00, false, 8, '2028-05-10', '{"13":{"category_id":13,"category_name":"General Education","category_type":"general_education","requirements":[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":26,"total_completed":177,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":100},"14":{"category_id":14,"category_name":"Major Core Requirements","category_type":"major","requirements":[{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS101","CS102"]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":0,"courses_remaining":3,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS201","CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]}],"total_required":0,"total_completed":0,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":0},"15":{"category_id":15,"category_name":"Major Electives","category_type":"major","requirements":[{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":12,"total_completed":59,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"17":{"category_id":17,"category_name":"Free Electives","category_type":"elective","requirements":[{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":15,"total_completed":59,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"18":{"category_id":18,"category_name":"University Requirements","category_type":"university","requirements":[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":49.166666666666664,"credits_required":120,"credits_completed":59,"credits_in_progress":0,"credits_remaining":61,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.83","gpa_type":"cumulative"},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":59,"of_last_credits":60}],"total_required":120,"total_completed":59,"total_in_progress":0,"total_remaining":61,"is_satisfied":false,"completion_percentage":49.166666666666664}}', '[{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.83","gpa_type":"cumulative"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":59,"of_last_credits":60}]', '[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":49.166666666666664,"credits_required":120,"credits_completed":59,"credits_in_progress":0,"credits_remaining":61,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}]', '[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS101","CS102"]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":0,"courses_remaining":3,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS201","CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"}]', '[{"type":"credits","priority":"medium","message":"Need 61 credits in Total Credit Hours","requirement":"Total Credit Hours"}]', 146, '2025-09-10 12:36:21', false, '2025-09-10 12:36:22', '2025-09-10 12:36:22');
INSERT INTO public.degree_audit_reports VALUES (4, 94, 1, 1, 'unofficial', '2025-2026', 120.0, 0.0, 0.0, 120.0, 0.00, 3.83, 3.83, 0.00, false, 8, '2028-05-10', '{"13":{"category_id":13,"category_name":"General Education","category_type":"general_education","requirements":[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":26,"total_completed":177,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":100},"14":{"category_id":14,"category_name":"Major Core Requirements","category_type":"major","requirements":[{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS101","CS102"]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":0,"courses_remaining":3,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS201","CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]}],"total_required":0,"total_completed":0,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":0},"15":{"category_id":15,"category_name":"Major Electives","category_type":"major","requirements":[{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":12,"total_completed":59,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"17":{"category_id":17,"category_name":"Free Electives","category_type":"elective","requirements":[{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":15,"total_completed":59,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"18":{"category_id":18,"category_name":"University Requirements","category_type":"university","requirements":[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":49.166666666666664,"credits_required":120,"credits_completed":59,"credits_in_progress":0,"credits_remaining":61,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.83","gpa_type":"cumulative"},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":59,"of_last_credits":60}],"total_required":120,"total_completed":59,"total_in_progress":0,"total_remaining":61,"is_satisfied":false,"completion_percentage":49.166666666666664}}', '[{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.83","gpa_type":"cumulative"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":59,"of_last_credits":60}]', '[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":49.166666666666664,"credits_required":120,"credits_completed":59,"credits_in_progress":0,"credits_remaining":61,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}]', '[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS101","CS102"]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":0,"courses_remaining":3,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS201","CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"}]', '[{"type":"credits","priority":"medium","message":"Need 61 credits in Total Credit Hours","requirement":"Total Credit Hours"}]', 146, '2025-09-10 12:36:21', false, '2025-09-10 12:36:22', '2025-09-10 12:36:22');
INSERT INTO public.degree_audit_reports VALUES (5, 94, 1, 1, 'unofficial', '2025-2026', 120.0, 0.0, 0.0, 120.0, 0.00, 3.83, 3.83, 0.00, false, 8, '2028-05-10', '{"13":{"category_id":13,"category_name":"General Education","category_type":"general_education","requirements":[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":26,"total_completed":177,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":100},"14":{"category_id":14,"category_name":"Major Core Requirements","category_type":"major","requirements":[{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS101","CS102"]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":0,"courses_remaining":3,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS201","CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]}],"total_required":0,"total_completed":0,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":0},"15":{"category_id":15,"category_name":"Major Electives","category_type":"major","requirements":[{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":12,"total_completed":59,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"17":{"category_id":17,"category_name":"Free Electives","category_type":"elective","requirements":[{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":15,"total_completed":59,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"18":{"category_id":18,"category_name":"University Requirements","category_type":"university","requirements":[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":49.166666666666664,"credits_required":120,"credits_completed":59,"credits_in_progress":0,"credits_remaining":61,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.83","gpa_type":"cumulative"},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":59,"of_last_credits":60}],"total_required":120,"total_completed":59,"total_in_progress":0,"total_remaining":61,"is_satisfied":false,"completion_percentage":49.166666666666664}}', '[{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.83","gpa_type":"cumulative"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":59,"of_last_credits":60}]', '[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":49.166666666666664,"credits_required":120,"credits_completed":59,"credits_in_progress":0,"credits_remaining":61,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}]', '[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS101","CS102"]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":0,"courses_remaining":3,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS201","CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"}]', '[{"type":"credits","priority":"medium","message":"Need 61 credits in Total Credit Hours","requirement":"Total Credit Hours"}]', 146, '2025-09-10 12:37:36', false, '2025-09-10 12:37:36', '2025-09-10 12:37:36');
INSERT INTO public.degree_audit_reports VALUES (6, 94, 1, 1, 'unofficial', '2025-2026', 120.0, 0.0, 0.0, 120.0, 0.00, 3.83, 3.83, 0.00, false, 8, '2028-05-10', '{"13":{"category_id":13,"category_name":"General Education","category_type":"general_education","requirements":[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":26,"total_completed":177,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":100},"14":{"category_id":14,"category_name":"Major Core Requirements","category_type":"major","requirements":[{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS101","CS102"]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":0,"courses_remaining":3,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS201","CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]}],"total_required":0,"total_completed":0,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":0},"15":{"category_id":15,"category_name":"Major Electives","category_type":"major","requirements":[{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":12,"total_completed":59,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"17":{"category_id":17,"category_name":"Free Electives","category_type":"elective","requirements":[{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":15,"total_completed":59,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"18":{"category_id":18,"category_name":"University Requirements","category_type":"university","requirements":[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":49.166666666666664,"credits_required":120,"credits_completed":59,"credits_in_progress":0,"credits_remaining":61,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.83","gpa_type":"cumulative"},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":59,"of_last_credits":60}],"total_required":120,"total_completed":59,"total_in_progress":0,"total_remaining":61,"is_satisfied":false,"completion_percentage":49.166666666666664}}', '[{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.83","gpa_type":"cumulative"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":59,"of_last_credits":60}]', '[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":49.166666666666664,"credits_required":120,"credits_completed":59,"credits_in_progress":0,"credits_remaining":61,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}]', '[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS101","CS102"]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":0,"courses_remaining":3,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS201","CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"}]', '[{"type":"credits","priority":"medium","message":"Need 61 credits in Total Credit Hours","requirement":"Total Credit Hours"}]', 146, '2025-09-10 13:51:33', false, '2025-09-10 13:51:34', '2025-09-10 13:51:34');
INSERT INTO public.degree_audit_reports VALUES (7, 94, 1, 1, 'unofficial', '2025-2026', 120.0, 0.0, 0.0, 120.0, 0.00, 3.83, 3.83, 0.00, false, 8, '2028-05-10', '{"13":{"category_id":13,"category_name":"General Education","category_type":"general_education","requirements":[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":26,"total_completed":177,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":100},"14":{"category_id":14,"category_name":"Major Core Requirements","category_type":"major","requirements":[{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS101","CS102"]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":0,"courses_remaining":3,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS201","CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]}],"total_required":0,"total_completed":0,"total_in_progress":0,"total_remaining":0,"is_satisfied":false,"completion_percentage":0},"15":{"category_id":15,"category_name":"Major Electives","category_type":"major","requirements":[{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":12,"total_completed":59,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"17":{"category_id":17,"category_name":"Free Electives","category_type":"elective","requirements":[{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}],"total_required":15,"total_completed":59,"total_in_progress":0,"total_remaining":0,"is_satisfied":true,"completion_percentage":100},"18":{"category_id":18,"category_name":"University Requirements","category_type":"university","requirements":[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":49.166666666666664,"credits_required":120,"credits_completed":59,"credits_in_progress":0,"credits_remaining":61,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.83","gpa_type":"cumulative"},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":59,"of_last_credits":60}],"total_required":120,"total_completed":59,"total_in_progress":0,"total_remaining":61,"is_satisfied":false,"completion_percentage":49.166666666666664}}', '[{"requirement_id":19,"requirement_name":"Natural Sciences","requirement_type":"credit_hours","requirement_description":"Natural science courses with lab","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":8,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":20,"requirement_name":"Humanities","requirement_type":"credit_hours","requirement_description":"Humanities and arts courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":21,"requirement_name":"Social Sciences","requirement_type":"credit_hours","requirement_description":"Social science courses","category_name":"General Education","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":9,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":27,"requirement_name":"Computer Science Electives","requirement_type":"credit_hours","requirement_description":"Upper-level CS elective courses","category_name":"Major Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":12,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":28,"requirement_name":"Free Electives","requirement_type":"credit_hours","requirement_description":"Additional elective credits","category_name":"Free Electives","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":15,"credits_completed":59,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0},{"requirement_id":30,"requirement_name":"Cumulative GPA","requirement_type":"gpa","requirement_description":"Minimum cumulative GPA","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":"3.83","gpa_type":"cumulative"},{"requirement_id":32,"requirement_name":"Residency Requirement","requirement_type":"residency","requirement_description":"Minimum credits at this institution","category_name":"University Requirements","is_required":true,"is_satisfied":true,"progress_percentage":100,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"residency_credits_required":30,"residency_credits_earned":59,"of_last_credits":60}]', '[{"requirement_id":29,"requirement_name":"Total Credit Hours","requirement_type":"credit_hours","requirement_description":"Minimum total credits for graduation","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":49.166666666666664,"credits_required":120,"credits_completed":59,"credits_in_progress":0,"credits_remaining":61,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0}]', '[{"requirement_id":17,"requirement_name":"English Composition","requirement_type":"specific_courses","requirement_description":"English composition and writing skills","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["ENG101","ENG102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["ENG101","ENG102"]},{"requirement_id":18,"requirement_name":"Mathematics","requirement_type":"course_list","requirement_description":"College-level mathematics","category_name":"General Education","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"course_options":["MATH151","MATH161","MATH171"],"completed_courses":[],"in_progress_courses":[]},{"requirement_id":22,"requirement_name":"Introduction to Computer Science","requirement_type":"specific_courses","requirement_description":"Introductory CS sequence","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS101","CS102"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS101","CS102"]},{"requirement_id":23,"requirement_name":"Programming Fundamentals","requirement_type":"specific_courses","requirement_description":"Core programming courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":3,"courses_completed":0,"courses_in_progress":0,"courses_remaining":3,"required_courses":["CS201","CS202","CS203"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS201","CS202","CS203"]},{"requirement_id":24,"requirement_name":"Computer Systems","requirement_type":"specific_courses","requirement_description":"Computer architecture and operating systems","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS301","CS302"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS301","CS302"]},{"requirement_id":25,"requirement_name":"Theoretical Computer Science","requirement_type":"specific_courses","requirement_description":"Algorithms and theory courses","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":2,"courses_completed":0,"courses_in_progress":0,"courses_remaining":2,"required_courses":["CS311","CS312"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS311","CS312"]},{"requirement_id":26,"requirement_name":"Senior Capstone","requirement_type":"specific_courses","requirement_description":"Senior capstone project","category_name":"Major Core Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":1,"courses_completed":0,"courses_in_progress":0,"courses_remaining":1,"required_courses":["CS490"],"completed_courses":[],"in_progress_courses":[],"remaining_courses":["CS490"]},{"requirement_id":31,"requirement_name":"Major GPA","requirement_type":"gpa","requirement_description":"Minimum GPA in major courses","category_name":"University Requirements","is_required":true,"is_satisfied":false,"progress_percentage":0,"credits_required":0,"credits_completed":0,"credits_in_progress":0,"credits_remaining":0,"courses_required":0,"courses_completed":0,"courses_in_progress":0,"courses_remaining":0,"required_gpa":2,"current_gpa":0,"gpa_type":"major"}]', '[{"type":"credits","priority":"medium","message":"Need 61 credits in Total Credit Hours","requirement":"Total Credit Hours"}]', 146, '2025-09-10 13:55:10', false, '2025-09-10 13:55:11', '2025-09-10 13:55:11');


--
-- Data for Name: degrees; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.degrees VALUES (1, 'Bachelor of Science', 'BS', 'undergraduate', 1, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (2, 'Bachelor of Arts', 'BA', 'undergraduate', 2, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (3, 'Bachelor of Business Administration', 'BBA', 'undergraduate', 3, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (4, 'Bachelor of Engineering', 'BE', 'undergraduate', 4, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (5, 'Bachelor of Technology', 'BTech', 'undergraduate', 5, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (6, 'Master of Science', 'MS', 'graduate', 10, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (7, 'Master of Arts', 'MA', 'graduate', 11, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (8, 'Master of Business Administration', 'MBA', 'graduate', 12, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (9, 'Master of Engineering', 'MEng', 'graduate', 13, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (10, 'Master of Technology', 'MTech', 'graduate', 14, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (11, 'Doctor of Philosophy', 'PhD', 'doctoral', 20, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (12, 'Doctor of Science', 'DSc', 'doctoral', 21, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.degrees VALUES (13, 'Doctor of Business Administration', 'DBA', 'doctoral', 22, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');


--
-- Data for Name: discussion_forums; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: discussion_posts; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: documents; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.documents VALUES (1, 'c4378d2b-497a-4e73-9e43-4b22353d70bf', 'legacy_b020a313cd493719f05b6145d48c7037', 'transcript_APP-2025-000008.pdf', 'Transcript', 'application/pdf', 4506536, 'documents/applications/30/transcript.pdf', 'local', 'admission', 'academic_record', NULL, NULL, '{"migrated_from":"application_documents","original_id":1,"document_type":"transcript","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'pending', NULL, '2025-09-12 15:49:18', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:57', '2025-09-15 13:32:57', NULL);
INSERT INTO public.documents VALUES (2, '86b2e61a-08a1-4f90-995a-a3d61fd13abc', 'legacy_2bca07a6ee5161dc26b83ac800cb3918', 'personal_statement_APP-2025-000008.pdf', 'Personal Statement', 'application/pdf', 3735643, 'documents/applications/30/personal_statement.pdf', 'local', 'admission', 'essay', NULL, NULL, '{"migrated_from":"application_documents","original_id":2,"document_type":"personal_statement","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, '2025-09-12 15:49:19', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (3, '2e86a0f0-39a9-47b6-944f-102e76eef60a', 'legacy_46ca021a2786e01e0d2bb13528c6113a', 'recommendation_letter_APP-2025-000008.pdf', 'Recommendation Letter', 'application/pdf', 4491682, 'documents/applications/30/recommendation_letter.pdf', 'local', 'admission', 'recommendation', NULL, NULL, '{"migrated_from":"application_documents","original_id":3,"document_type":"recommendation_letter","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending', 'verified', NULL, '2025-09-12 15:49:19', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (4, 'ac0bed81-1bd0-4994-89cd-58f7886c0dc9', 'legacy_4b5732907c540eeab69ca1874d440ffe', 'test_scores_APP-2025-000008.pdf', 'Test Scores', 'application/pdf', 2374255, 'documents/applications/30/test_scores.pdf', 'local', 'admission', 'test_result', NULL, NULL, '{"migrated_from":"application_documents","original_id":4,"document_type":"test_scores","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (5, '8cd69cc1-cf5a-45b4-88e4-67bdeec12a6b', 'legacy_bb9fff614b6a9f9c97a8e86d6c1b49ad', 'passport_APP-2025-000008.pdf', 'Passport', 'application/pdf', 841984, 'documents/applications/30/passport.pdf', 'local', 'admission', 'identity', NULL, NULL, '{"migrated_from":"application_documents","original_id":5,"document_type":"passport","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (6, 'a3d09182-9de2-4205-af1e-0d95708144a4', 'legacy_ea51162dd7de1f8c9f4581677301db51', 'personal_statement_APP-2025-000021.pdf', 'Personal Statement', 'application/pdf', 1890057, 'documents/applications/43/personal_statement.pdf', 'local', 'admission', 'essay', NULL, NULL, '{"migrated_from":"application_documents","original_id":6,"document_type":"personal_statement","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (7, 'e2368a64-e66f-4766-98bc-06b975608e8b', 'legacy_d322ccd5ea91042a4d5bf0cddd6bb025', 'recommendation_letter_APP-2025-000021.pdf', 'Recommendation Letter', 'application/pdf', 477424, 'documents/applications/43/recommendation_letter.pdf', 'local', 'admission', 'recommendation', NULL, NULL, '{"migrated_from":"application_documents","original_id":7,"document_type":"recommendation_letter","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'pending', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (8, 'a2295f49-5295-4dc3-9e7a-445d514bc15a', 'legacy_097f8f426276c3a3768f0b273fef9c0d', 'test_scores_APP-2025-000021.pdf', 'Test Scores', 'application/pdf', 926145, 'documents/applications/43/test_scores.pdf', 'local', 'admission', 'test_result', NULL, NULL, '{"migrated_from":"application_documents","original_id":8,"document_type":"test_scores","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, '2025-09-12 15:49:19', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (9, 'bd79ed7f-b1d1-4117-9d67-7ab5d6362c8c', 'legacy_8739f081d29dc08a59c06b8c623053b6', 'passport_APP-2025-000021.pdf', 'Passport', 'application/pdf', 4909078, 'documents/applications/43/passport.pdf', 'local', 'admission', 'identity', NULL, NULL, '{"migrated_from":"application_documents","original_id":9,"document_type":"passport","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'pending', NULL, '2025-09-12 15:49:19', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (10, '23c27f6f-2f0a-405b-b4b0-3f3c6dfb1e0a', 'legacy_b3ae62c30816b7be0874cfc1c757fffd', 'transcript_APP-2025-000003.pdf', 'Transcript', 'application/pdf', 1208771, 'documents/applications/25/transcript.pdf', 'local', 'admission', 'academic_record', NULL, NULL, '{"migrated_from":"application_documents","original_id":10,"document_type":"transcript","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (11, '9041c392-d1e7-4e4e-b81a-b36211263be3', 'legacy_8a13f63bde5b37ff7383b52842d782b5', 'personal_statement_APP-2025-000003.pdf', 'Personal Statement', 'application/pdf', 4173749, 'documents/applications/25/personal_statement.pdf', 'local', 'admission', 'essay', NULL, NULL, '{"migrated_from":"application_documents","original_id":11,"document_type":"personal_statement","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'pending', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (12, '5b72bad1-5276-41b1-b88d-e3dfd47ccaad', 'legacy_a1f64183ebe5717f4c71489e291fee8c', 'recommendation_letter_APP-2025-000003.pdf', 'Recommendation Letter', 'application/pdf', 2512577, 'documents/applications/25/recommendation_letter.pdf', 'local', 'admission', 'recommendation', NULL, NULL, '{"migrated_from":"application_documents","original_id":12,"document_type":"recommendation_letter","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, '2025-09-12 15:49:19', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (13, '16609e60-8214-4b6b-af0e-53bba1bf0288', 'legacy_88a1c0c55c261c177b5353a619fdb2f2', 'test_scores_APP-2025-000003.pdf', 'Test Scores', 'application/pdf', 4270587, 'documents/applications/25/test_scores.pdf', 'local', 'admission', 'test_result', NULL, NULL, '{"migrated_from":"application_documents","original_id":13,"document_type":"test_scores","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (14, '653909e7-eab4-452a-8cb1-434a4ae513c6', 'legacy_0cd2a0ad9c8d0acca8149e886c06b40c', 'personal_statement_APP-2025-000004.pdf', 'Personal Statement', 'application/pdf', 2536737, 'documents/applications/26/personal_statement.pdf', 'local', 'admission', 'essay', NULL, NULL, '{"migrated_from":"application_documents","original_id":14,"document_type":"personal_statement","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, '2025-09-12 15:49:19', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (15, 'f7607d66-e172-4a8e-bdd4-9bd9eda4405d', 'legacy_7b26dadb080d4d76e40e08ea00d1fbf9', 'recommendation_letter_APP-2025-000004.pdf', 'Recommendation Letter', 'application/pdf', 4415279, 'documents/applications/26/recommendation_letter.pdf', 'local', 'admission', 'recommendation', NULL, NULL, '{"migrated_from":"application_documents","original_id":15,"document_type":"recommendation_letter","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'pending', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (16, '6f72e470-b63a-47f0-9629-c7579b3867f5', 'legacy_ce8321da95c42945fff795e951dbe888', 'test_scores_APP-2025-000004.pdf', 'Test Scores', 'application/pdf', 3194698, 'documents/applications/26/test_scores.pdf', 'local', 'admission', 'test_result', NULL, NULL, '{"migrated_from":"application_documents","original_id":16,"document_type":"test_scores","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (17, '2ae433e7-a9c3-40bd-868a-8dae7fd64136', 'legacy_ed2d741255b6f54cf27848b4a9403494', 'transcript_APP-2025-000005.pdf', 'Transcript', 'application/pdf', 3577049, 'documents/applications/27/transcript.pdf', 'local', 'admission', 'academic_record', NULL, NULL, '{"migrated_from":"application_documents","original_id":17,"document_type":"transcript","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (18, '86e449ed-b652-4f93-90a3-b843f32f716e', 'legacy_86c380a0dedd0c1f4d21707e0ca08903', 'personal_statement_APP-2025-000005.pdf', 'Personal Statement', 'application/pdf', 2267577, 'documents/applications/27/personal_statement.pdf', 'local', 'admission', 'essay', NULL, NULL, '{"migrated_from":"application_documents","original_id":18,"document_type":"personal_statement","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'pending', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (19, '21fce994-09f4-419e-bf67-2f32ca26b40a', 'legacy_fb7d0e55453998d26fa5b4d676d65813', 'recommendation_letter_APP-2025-000005.pdf', 'Recommendation Letter', 'application/pdf', 4669682, 'documents/applications/27/recommendation_letter.pdf', 'local', 'admission', 'recommendation', NULL, NULL, '{"migrated_from":"application_documents","original_id":19,"document_type":"recommendation_letter","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (20, '7a10741e-4338-4ff3-8125-83f8e14d03a6', 'legacy_3d5af8efdb9504c56a8be776952a0971', 'test_scores_APP-2025-000005.pdf', 'Test Scores', 'application/pdf', 2482988, 'documents/applications/27/test_scores.pdf', 'local', 'admission', 'test_result', NULL, NULL, '{"migrated_from":"application_documents","original_id":20,"document_type":"test_scores","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'pending', NULL, '2025-09-12 15:49:19', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (21, '1a988c24-5114-4ad5-9f9e-1cb0e22423b8', 'legacy_0c3e8d067337344ed3ec568088fb1117', 'transcript_APP-2025-000006.pdf', 'Transcript', 'application/pdf', 847744, 'documents/applications/28/transcript.pdf', 'local', 'admission', 'academic_record', NULL, NULL, '{"migrated_from":"application_documents","original_id":21,"document_type":"transcript","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (22, 'f55b9762-3e1e-4607-a064-e3dff9c39596', 'legacy_f69699b1b303e41b572c68fad93bb295', 'personal_statement_APP-2025-000006.pdf', 'Personal Statement', 'application/pdf', 627442, 'documents/applications/28/personal_statement.pdf', 'local', 'admission', 'essay', NULL, NULL, '{"migrated_from":"application_documents","original_id":22,"document_type":"personal_statement","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (23, '51fa8e38-9dd2-4535-ba2e-ea39d48df3ba', 'legacy_62478d5e6efd278056258858f7ac9f7b', 'recommendation_letter_APP-2025-000006.pdf', 'Recommendation Letter', 'application/pdf', 2900783, 'documents/applications/28/recommendation_letter.pdf', 'local', 'admission', 'recommendation', NULL, NULL, '{"migrated_from":"application_documents","original_id":23,"document_type":"recommendation_letter","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'pending', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (24, 'ff3e6245-d543-4be0-8a5b-487ea315fdb2', 'legacy_21299965d0ebcb026e7eb5bb1add2ef2', 'test_scores_APP-2025-000006.pdf', 'Test Scores', 'application/pdf', 2118011, 'documents/applications/28/test_scores.pdf', 'local', 'admission', 'test_result', NULL, NULL, '{"migrated_from":"application_documents","original_id":24,"document_type":"test_scores","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'verified', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (25, '9b01fed8-6537-4309-b18b-2a62966220e9', 'legacy_4233392a1a1ce1c77e8280e17f132e28', 'passport_APP-2025-000006.pdf', 'Passport', 'application/pdf', 2642020, 'documents/applications/28/passport.pdf', 'local', 'admission', 'identity', NULL, NULL, '{"migrated_from":"application_documents","original_id":25,"document_type":"passport","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'verified', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (26, 'c7a1179a-ea1f-4607-bcf0-45925eaa7bdc', 'legacy_5f9f5d16b6d23bf5661f5784e75c44e2', 'transcript_APP-2025-000007.pdf', 'Transcript', 'application/pdf', 848825, 'documents/applications/29/transcript.pdf', 'local', 'admission', 'academic_record', NULL, NULL, '{"migrated_from":"application_documents","original_id":26,"document_type":"transcript","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'pending', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (27, '0075addd-14a4-46e6-a22c-e95d9f0185fe', 'legacy_125e4c356c06791bd274d1fff75428a1', 'personal_statement_APP-2025-000007.pdf', 'Personal Statement', 'application/pdf', 4863993, 'documents/applications/29/personal_statement.pdf', 'local', 'admission', 'essay', NULL, NULL, '{"migrated_from":"application_documents","original_id":27,"document_type":"personal_statement","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending', 'pending', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (28, '9b61421a-e1a1-45e8-89e6-3e1c45d9f08e', 'legacy_b101d3bf876b7a71cc6338a4cd4dfc59', 'recommendation_letter_APP-2025-000007.pdf', 'Recommendation Letter', 'application/pdf', 2407407, 'documents/applications/29/recommendation_letter.pdf', 'local', 'admission', 'recommendation', NULL, NULL, '{"migrated_from":"application_documents","original_id":28,"document_type":"recommendation_letter","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (29, '10453d4a-6be6-46a5-87bb-4779204ec505', 'legacy_175c66514adb4c27605cb1cdc244fe3d', 'passport_APP-2025-000007.pdf', 'Passport', 'application/pdf', 2018683, 'documents/applications/29/passport.pdf', 'local', 'admission', 'identity', NULL, NULL, '{"migrated_from":"application_documents","original_id":29,"document_type":"passport","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'verified', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (30, '143edc49-0415-4665-b7e2-b0b28579187f', 'legacy_154fefce100e1a686bdbd76df1e54a32', 'recommendation_letter_APP-2025-000009.pdf', 'Recommendation Letter', 'application/pdf', 2720817, 'documents/applications/31/recommendation_letter.pdf', 'local', 'admission', 'recommendation', NULL, NULL, '{"migrated_from":"application_documents","original_id":30,"document_type":"recommendation_letter","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending', 'verified', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (31, 'c01e96b8-15c2-489c-8ccd-bd6f37c293fb', 'legacy_1f4c0b52a691b1c45be1441e2205f0a6', 'test_scores_APP-2025-000009.pdf', 'Test Scores', 'application/pdf', 2222961, 'documents/applications/31/test_scores.pdf', 'local', 'admission', 'test_result', NULL, NULL, '{"migrated_from":"application_documents","original_id":31,"document_type":"test_scores","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (32, '9b8962b8-acde-4031-8704-e0893ec3b391', 'legacy_0f2edef155e0c6d6b0c1a7927c3526c5', 'passport_APP-2025-000009.pdf', 'Passport', 'application/pdf', 643433, 'documents/applications/31/passport.pdf', 'local', 'admission', 'identity', NULL, NULL, '{"migrated_from":"application_documents","original_id":32,"document_type":"passport","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending', 'verified', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (33, 'cf126bae-cc42-4edd-bd33-cbb9062721e7', 'legacy_462e7af43bf23b9e065d1674f194586f', 'transcript_APP-2025-000010.pdf', 'Transcript', 'application/pdf', 2698121, 'documents/applications/32/transcript.pdf', 'local', 'admission', 'academic_record', NULL, NULL, '{"migrated_from":"application_documents","original_id":33,"document_type":"transcript","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending', 'pending', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (34, 'd575e0f0-edd1-4fc2-98a3-3231cb91db19', 'legacy_a66e9f2df60b7ee7979dce7d10a1062d', 'personal_statement_APP-2025-000010.pdf', 'Personal Statement', 'application/pdf', 2864105, 'documents/applications/32/personal_statement.pdf', 'local', 'admission', 'essay', NULL, NULL, '{"migrated_from":"application_documents","original_id":34,"document_type":"personal_statement","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (35, '16596284-d257-4ad0-8261-8231ba67be6a', 'legacy_0c7f9f76c08322e63e253a0657749a7d', 'passport_APP-2025-000010.pdf', 'Passport', 'application/pdf', 1254605, 'documents/applications/32/passport.pdf', 'local', 'admission', 'identity', NULL, NULL, '{"migrated_from":"application_documents","original_id":35,"document_type":"passport","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending', 'verified', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (36, '6c90b7ce-ad8f-4aaa-9efc-700e6b92a114', 'legacy_4ae4433e68b0b888cf07ffb2a2874b73', 'transcript_APP-2025-000011.pdf', 'Transcript', 'application/pdf', 4424594, 'documents/applications/33/transcript.pdf', 'local', 'admission', 'academic_record', NULL, NULL, '{"migrated_from":"application_documents","original_id":36,"document_type":"transcript","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (37, '376fdd50-a512-4faf-a506-ed19f71097b5', 'legacy_9bf421340f565b5a90d77bd1ad74439c', 'personal_statement_APP-2025-000011.pdf', 'Personal Statement', 'application/pdf', 4941035, 'documents/applications/33/personal_statement.pdf', 'local', 'admission', 'essay', NULL, NULL, '{"migrated_from":"application_documents","original_id":37,"document_type":"personal_statement","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending_verification', 'verified', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (38, 'b993e7bc-e2d4-4845-a92e-77f50c8132f5', 'legacy_383b6a56ca309b6a345f349363bd2c8e', 'recommendation_letter_APP-2025-000011.pdf', 'Recommendation Letter', 'application/pdf', 2479175, 'documents/applications/33/recommendation_letter.pdf', 'local', 'admission', 'recommendation', NULL, NULL, '{"migrated_from":"application_documents","original_id":38,"document_type":"recommendation_letter","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending', 'pending', NULL, '2025-09-12 15:49:20', NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (39, '9ce682dc-5300-4e4c-9148-5145e7752ab7', 'legacy_2e9fa4d48e261c27101b8b97b2f9cfd0', 'test_scores_APP-2025-000011.pdf', 'Test Scores', 'application/pdf', 3800414, 'documents/applications/33/test_scores.pdf', 'local', 'admission', 'test_result', NULL, NULL, '{"migrated_from":"application_documents","original_id":39,"document_type":"test_scores","recommender_name":null,"recommender_email":null,"recommender_title":null,"recommender_institution":null}', 'pending', 'verified', NULL, NULL, NULL, NULL, 'restricted', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58', NULL);
INSERT INTO public.documents VALUES (42, '1279c8ab-cac8-4fbe-8e49-09c6947510ac', 'f10e232b3a51a04cd819b432b580c6a5f84e9be0c1a84d4334a4c0c68055890c', 'test.txt', 'test.txt', 'text/plain', 48, 'documents/2025/09/15/f10e232b3a51a04cd819b432b580c6a5f84e9be0c1a84d4334a4c0c68055890c.txt', 'local', 'admission', 'test', NULL, '[]', '[]', 'active', 'not_required', NULL, NULL, NULL, NULL, 'private', true, NULL, false, false, false, false, NULL, NULL, NULL, false, NULL, 0, 0, NULL, 1, '172.18.0.1', NULL, '2025-09-15 14:10:37', '2025-09-15 14:10:37', NULL);


--
-- Data for Name: document_access_logs; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.document_access_logs VALUES (2, 42, 1, 'upload', NULL, '172.18.0.1', 'curl/8.14.1', '{"context":"admission","owner_id":1}', '2025-09-15 14:10:37');


--
-- Data for Name: document_processing_queue; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.document_processing_queue VALUES (3, 42, 'virus_scan', 'pending', 0, 3, '{"category":"test","requires_verification":false}', NULL, NULL, NULL, NULL, '2025-09-15 14:10:37', '2025-09-15 14:10:37');
INSERT INTO public.document_processing_queue VALUES (4, 42, 'extract_metadata', 'pending', 0, 3, '{"category":"test","requires_verification":false}', NULL, NULL, NULL, NULL, '2025-09-15 14:10:37', '2025-09-15 14:10:37');


--
-- Data for Name: document_relationships; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.document_relationships VALUES (1, 1, 'application', 30, 'attachment', 'transcript', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:57', '2025-09-15 13:32:57');
INSERT INTO public.document_relationships VALUES (2, 2, 'application', 30, 'attachment', 'personal_statement', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (3, 3, 'application', 30, 'attachment', 'recommendation_letter', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (4, 4, 'application', 30, 'attachment', 'test_scores', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (5, 5, 'application', 30, 'attachment', 'passport', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (6, 6, 'application', 43, 'attachment', 'personal_statement', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (7, 7, 'application', 43, 'attachment', 'recommendation_letter', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (8, 8, 'application', 43, 'attachment', 'test_scores', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (9, 9, 'application', 43, 'attachment', 'passport', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (10, 10, 'application', 25, 'attachment', 'transcript', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (11, 11, 'application', 25, 'attachment', 'personal_statement', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (12, 12, 'application', 25, 'attachment', 'recommendation_letter', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (13, 13, 'application', 25, 'attachment', 'test_scores', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (14, 14, 'application', 26, 'attachment', 'personal_statement', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (15, 15, 'application', 26, 'attachment', 'recommendation_letter', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (16, 16, 'application', 26, 'attachment', 'test_scores', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (17, 17, 'application', 27, 'attachment', 'transcript', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (18, 18, 'application', 27, 'attachment', 'personal_statement', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (19, 19, 'application', 27, 'attachment', 'recommendation_letter', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (20, 20, 'application', 27, 'attachment', 'test_scores', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (21, 21, 'application', 28, 'attachment', 'transcript', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (22, 22, 'application', 28, 'attachment', 'personal_statement', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (23, 23, 'application', 28, 'attachment', 'recommendation_letter', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (24, 24, 'application', 28, 'attachment', 'test_scores', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (25, 25, 'application', 28, 'attachment', 'passport', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (26, 26, 'application', 29, 'attachment', 'transcript', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (27, 27, 'application', 29, 'attachment', 'personal_statement', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (28, 28, 'application', 29, 'attachment', 'recommendation_letter', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (29, 29, 'application', 29, 'attachment', 'passport', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (30, 30, 'application', 31, 'attachment', 'recommendation_letter', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (31, 31, 'application', 31, 'attachment', 'test_scores', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (32, 32, 'application', 31, 'attachment', 'passport', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (33, 33, 'application', 32, 'attachment', 'transcript', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (34, 34, 'application', 32, 'attachment', 'personal_statement', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (35, 35, 'application', 32, 'attachment', 'passport', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (36, 36, 'application', 33, 'attachment', 'transcript', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (37, 37, 'application', 33, 'attachment', 'personal_statement', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (38, 38, 'application', 33, 'attachment', 'recommendation_letter', 0, 'read', false, false, false, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (39, 39, 'application', 33, 'attachment', 'test_scores', 0, 'read', false, false, true, NULL, NULL, '2025-09-15 13:32:58', '2025-09-15 13:32:58');
INSERT INTO public.document_relationships VALUES (41, 42, 'admission', 1, 'owner', NULL, 0, 'read', false, false, false, NULL, NULL, '2025-09-15 14:10:37', '2025-09-15 14:10:37');
INSERT INTO public.document_relationships VALUES (42, 42, 'admission', 2, 'owner', NULL, 0, 'read', false, false, false, NULL, NULL, '2025-09-15 14:11:22', '2025-09-15 14:11:22');


--
-- Data for Name: document_requests; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: document_templates; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: document_versions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: email_templates; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: enrollment_confirmations; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: enrollment_histories; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: enrollments; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.enrollments VALUES (6, 1, 1, 1, 'enrolled', '2025-09-08', NULL, NULL, NULL, 'in-person', '2025-09-08 14:06:04', '2025-09-08 14:06:04', NULL, 'graded', NULL, NULL, NULL, NULL, 'enrolled');
INSERT INTO public.enrollments VALUES (7, 11, 1, 1, 'enrolled', '2025-09-08', NULL, NULL, NULL, 'in-person', '2025-09-08 14:09:31', '2025-09-08 14:09:31', NULL, 'graded', NULL, NULL, NULL, NULL, 'enrolled');
INSERT INTO public.enrollments VALUES (8, 11, 2, 1, 'enrolled', '2025-09-08', NULL, NULL, NULL, 'in-person', '2025-09-08 14:09:31', '2025-09-08 14:09:31', NULL, 'graded', NULL, NULL, NULL, NULL, 'enrolled');
INSERT INTO public.enrollments VALUES (9, 11, 3, 1, 'enrolled', '2025-09-08', NULL, NULL, NULL, 'in-person', '2025-09-08 14:09:31', '2025-09-08 14:09:31', NULL, 'graded', NULL, NULL, NULL, NULL, 'enrolled');


--
-- Data for Name: exam_responses; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: entrance_exam_results; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: exam_certificates; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: exam_proctoring_logs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: exam_response_details; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: exam_seat_allocations; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: faculty_availability; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: faculty_course_assignments; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: final_grades; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: financial_aid; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: financial_holds_history; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: financial_transactions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: grade_components; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: grade_audit_log; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: grade_change_requests; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: grade_deadlines; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.grade_deadlines VALUES (1, 1, 'midterm', '2025-10-27', '23:59:59', 'Midterm grade submission deadline', true, 3, '2025-08-31 07:04:43', '2025-08-31 07:04:43');
INSERT INTO public.grade_deadlines VALUES (2, 1, 'final', '2025-12-18', '23:59:59', 'Final grade submission deadline', true, 5, '2025-08-31 07:04:43', '2025-08-31 07:04:43');
INSERT INTO public.grade_deadlines VALUES (3, 1, 'incomplete', '2026-01-12', '23:59:59', 'Incomplete grade resolution deadline', true, 7, '2025-08-31 07:04:43', '2025-08-31 07:04:43');
INSERT INTO public.grade_deadlines VALUES (5, 1, 'grade_change', '2026-01-14', '23:59:59', 'Grade change request deadline', true, 7, '2025-08-31 10:44:11', '2025-08-31 10:44:11');


--
-- Data for Name: grade_scales; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.grade_scales VALUES (3, 'Standard Letter Grade Scale', '{"A":{"min":93,"max":100,"points":4},"A-":{"min":90,"max":92.99,"points":3.7},"B+":{"min":87,"max":89.99,"points":3.3},"B":{"min":83,"max":86.99,"points":3},"B-":{"min":80,"max":82.99,"points":2.7},"C+":{"min":77,"max":79.99,"points":2.3},"C":{"min":73,"max":76.99,"points":2},"C-":{"min":70,"max":72.99,"points":1.7},"D+":{"min":67,"max":69.99,"points":1.3},"D":{"min":63,"max":66.99,"points":1},"F":{"min":0,"max":62.99,"points":0},"W":{"points":0,"special":true},"I":{"points":0,"special":true},"P":{"points":0,"special":true},"NP":{"points":0,"special":true},"AU":{"points":0,"special":true},"IP":{"points":0,"special":true}}', true, 'Standard grading scale used for all undergraduate courses', '2025-08-31 06:23:41', '2025-08-31 06:23:41');
INSERT INTO public.grade_scales VALUES (4, 'Pass/Fail Scale', '{"P":{"min":70,"max":100,"points":0},"NP":{"min":0,"max":69.99,"points":0}}', true, 'Pass/Fail grading for specific courses', '2025-08-31 06:23:41', '2025-08-31 06:23:41');
INSERT INTO public.grade_scales VALUES (1, 'Standard Letter Grade Scale', '{"A":{"min":93,"max":100,"points":4},"A-":{"min":90,"max":92.99,"points":3.7},"B+":{"min":87,"max":89.99,"points":3.3},"B":{"min":83,"max":86.99,"points":3},"B-":{"min":80,"max":82.99,"points":2.7},"C+":{"min":77,"max":79.99,"points":2.3},"C":{"min":73,"max":76.99,"points":2},"C-":{"min":70,"max":72.99,"points":1.7},"D+":{"min":67,"max":69.99,"points":1.3},"D":{"min":63,"max":66.99,"points":1},"F":{"min":0,"max":62.99,"points":0},"W":{"points":0,"special":true,"description":"Withdrawal"},"I":{"points":0,"special":true,"description":"Incomplete"},"P":{"points":0,"special":true,"description":"Pass"},"NP":{"points":0,"special":true,"description":"No Pass"},"AU":{"points":0,"special":true,"description":"Audit"},"IP":{"points":0,"special":true,"description":"In Progress"}}', true, 'Standard grading scale used for all undergraduate courses', '2025-08-31 07:04:43', '2025-08-31 07:04:43');
INSERT INTO public.grade_scales VALUES (2, 'Pass/Fail Scale', '{"P":{"min":70,"max":100,"points":0},"NP":{"min":0,"max":69.99,"points":0}}', true, 'Pass/Fail grading for specific courses', '2025-08-31 07:04:43', '2025-08-31 07:04:43');
INSERT INTO public.grade_scales VALUES (5, 'Graduate Scale', '{"A":{"min":95,"max":100,"points":4},"A-":{"min":92,"max":94.99,"points":3.7},"B+":{"min":89,"max":91.99,"points":3.3},"B":{"min":85,"max":88.99,"points":3},"B-":{"min":82,"max":84.99,"points":2.7},"C+":{"min":79,"max":81.99,"points":2.3},"C":{"min":75,"max":78.99,"points":2},"F":{"min":0,"max":74.99,"points":0}}', true, 'Grading scale for graduate programs', '2025-08-31 07:04:43', '2025-08-31 07:04:43');


--
-- Data for Name: grade_submissions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: gradebook_items; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: gradebook_entries; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: grades; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: grading_configurations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.grading_configurations VALUES (1, 'letter', 4.00, 2.00, 2.00, 3.50, 3.80, NULL, NULL, true, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');


--
-- Data for Name: graduation_applications; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: import_logs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: institution_config; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.institution_config VALUES (1, 'IntelliCampus University', 'ICU', 'university', '123 Education Boulevard', 'Knowledge City', 'Learning State', 'Liberia', '1000', '+231775711477', 'info@intellicampus.edu', 'https://www.intellicampus.edu', NULL, 'America/New_York', 'USD', '$', 'Y-m-d', 'H:i', NULL, NULL, true, '2025-09-07 09:58:49', '2025-09-07 16:09:12');


--
-- Data for Name: invoices; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.invoices VALUES (1, 'INV-2025-000001', 11, 1, '2025-09-02', '2025-10-02', 3700.00, 0.00, 3700.00, 'sent', '[{"billing_item_id":82,"description":"Registration Fee","amount":"150.00","balance":"150.00","due_date":"2025-10-02"},{"billing_item_id":83,"description":"Technology Fee","amount":"100.00","balance":"100.00","due_date":"2025-10-02"},{"billing_item_id":84,"description":"Health Services Fee","amount":"200.00","balance":"200.00","due_date":"2025-10-02"},{"billing_item_id":81,"description":"Tuition - Fall 2025 (15 credits)","amount":"5250.00","balance":"3250.00","due_date":"2025-10-02"}]', NULL, NULL, NULL, NULL, '2025-09-02 22:35:00', '2025-09-02 22:35:12');


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: lms_announcements; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.migrations VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO public.migrations VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO public.migrations VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO public.migrations VALUES (4, '2024_01_01_000000_create_students_table', 1);
INSERT INTO public.migrations VALUES (5, '2024_01_02_000000_create_roles_table', 1);
INSERT INTO public.migrations VALUES (6, '2024_01_02_000001_create_permissions_table', 1);
INSERT INTO public.migrations VALUES (7, '2024_01_02_000002_create_role_user_and_permission_role_tables', 1);
INSERT INTO public.migrations VALUES (8, '2024_01_02_000003_add_user_management_fields_to_users_table', 1);
INSERT INTO public.migrations VALUES (9, '2024_01_02_000004_create_user_activity_logs_table', 1);
INSERT INTO public.migrations VALUES (10, '2025_08_24_080949_create_personal_access_tokens_table', 1);
INSERT INTO public.migrations VALUES (11, '2025_08_24_082109_create_enrollment_histories_table', 1);
INSERT INTO public.migrations VALUES (12, '2025_08_24_085747_create_import_logs_table', 1);
INSERT INTO public.migrations VALUES (13, '2025_08_24_152211_create_student_status_changes_table', 1);
INSERT INTO public.migrations VALUES (14, '2024_01_20_000001_create_academic_programs_table', 2);
INSERT INTO public.migrations VALUES (15, '2024_01_20_000002_create_courses_table', 2);
INSERT INTO public.migrations VALUES (16, '2024_01_20_000003_create_course_prerequisites_table', 2);
INSERT INTO public.migrations VALUES (17, '2024_01_20_000004_create_program_courses_table', 2);
INSERT INTO public.migrations VALUES (18, '2024_01_20_000005_create_academic_terms_table', 2);
INSERT INTO public.migrations VALUES (19, '2024_01_20_000006_create_course_sections_table', 2);
INSERT INTO public.migrations VALUES (20, '2024_01_20_000007_create_section_schedules_table', 2);
INSERT INTO public.migrations VALUES (28, '2025_01_01_000001_create_enrollments_table', 3);
INSERT INTO public.migrations VALUES (29, '2025_01_01_000002_create_attendance_table', 3);
INSERT INTO public.migrations VALUES (30, '2025_01_01_000003_create_grades_table', 3);
INSERT INTO public.migrations VALUES (31, '2025_01_01_000004_create_grade_components_table', 3);
INSERT INTO public.migrations VALUES (32, '2025_01_01_000005_create_office_hours_table', 3);
INSERT INTO public.migrations VALUES (33, '2025_01_01_000006_create_office_appointments_table', 3);
INSERT INTO public.migrations VALUES (34, '2025_01_01_000007_create_announcements_table', 3);
INSERT INTO public.migrations VALUES (35, '2025_08_25_191537_create_registration_system_tables', 3);
INSERT INTO public.migrations VALUES (36, '2025_08_26_000000_fix_students_table_nullable_columns', 4);
INSERT INTO public.migrations VALUES (37, '2025_08_26_072741_create_registration_enhancement_tables', 5);
INSERT INTO public.migrations VALUES (38, '2025_08_26_074444_create_registration_enhancement_tables', 6);
INSERT INTO public.migrations VALUES (39, '2025_08_26_075506_recreate_registration_periods_table_with_correct_structure', 7);
INSERT INTO public.migrations VALUES (40, '2025_08_26_082127_add_is_active_to_course_prerequisites_table', 8);
INSERT INTO public.migrations VALUES (41, '2025_08_26_092710_fix_registration_logs_table_term_id', 9);
INSERT INTO public.migrations VALUES (42, '2025_08_27_234113_create_additional_grade_tables', 10);
INSERT INTO public.migrations VALUES (43, '2025_08_28_000000_update_grade_components_types', 11);
INSERT INTO public.migrations VALUES (44, '2025_08_31_000001_rebuild_grades_table', 12);
INSERT INTO public.migrations VALUES (45, '2025_08_31_120000_fix_grade_deadlines_enum', 13);
INSERT INTO public.migrations VALUES (46, '2024_12_30_fix_grade_components_table', 14);
INSERT INTO public.migrations VALUES (47, '2024_01_15_create_programs_table', 15);
INSERT INTO public.migrations VALUES (48, '2024_12_03_fix_registration_cart_structure', 16);
INSERT INTO public.migrations VALUES (49, '2024_12_03_create_financial_management_system', 17);
INSERT INTO public.migrations VALUES (50, '2024_12_03_add_applied_flag_to_payments', 18);
INSERT INTO public.migrations VALUES (51, '2024_12_03_add_soft_deletes_to_payments', 19);
INSERT INTO public.migrations VALUES (52, '2024_12_03_add_missing_payment_columns', 20);
INSERT INTO public.migrations VALUES (53, '2025_01_XX_000001_create_colleges_table', 21);
INSERT INTO public.migrations VALUES (54, '2025_01_XX_000002_create_schools_table', 21);
INSERT INTO public.migrations VALUES (55, '2025_01_XX_000003_create_departments_table', 22);
INSERT INTO public.migrations VALUES (56, '2025_01_XX_000004_create_divisions_table', 23);
INSERT INTO public.migrations VALUES (57, '2025_01_XX_000005_add_organizational_fields_to_users_table', 23);
INSERT INTO public.migrations VALUES (58, '2025_09_04_171830_add_organizational_fields_to_departments_table', 24);
INSERT INTO public.migrations VALUES (59, '2025_01_XX_000006_add_organizational_fields_to_courses_table', 25);
INSERT INTO public.migrations VALUES (60, '2025_01_XX_000007_create_user_department_affiliations_table', 25);
INSERT INTO public.migrations VALUES (61, '2025_01_XX_000008_create_faculty_course_assignments_table', 25);
INSERT INTO public.migrations VALUES (62, '2025_01_XX_000009_create_organizational_permissions_table', 25);
INSERT INTO public.migrations VALUES (63, '2025_01_XX_000010_create_scope_audit_logs_table', 25);
INSERT INTO public.migrations VALUES (64, '2025_09_04_175515_fix_departments_table_add_missing_organizational_fields', 26);
INSERT INTO public.migrations VALUES (65, '2025_09_06_002856_fix_student_constraints_comprehensive', 27);
INSERT INTO public.migrations VALUES (66, '2025_09_06_041030_add_per_credit_amount_to_fee_structures_table', 27);
INSERT INTO public.migrations VALUES (67, '2025_01_16_000001_create_lms_tables_fixed', 28);
INSERT INTO public.migrations VALUES (68, '2025_01_16_fix_courses_department_column', 99);
INSERT INTO public.migrations VALUES (69, '2025_09_06_210342_create_complete_transcript_system', 100);
INSERT INTO public.migrations VALUES (70, '2025_01_10_000001_create_system_configuration_tables', 101);
INSERT INTO public.migrations VALUES (71, '2025_01_10_000002_create_attendance_management_tables', 102);
INSERT INTO public.migrations VALUES (72, '2025_01_10_000003_create_scheduling_timetabling_tables', 103);
INSERT INTO public.migrations VALUES (73, '2025_01_20_create_payment_gateway_tables', 104);
INSERT INTO public.migrations VALUES (74, '2025_09_07_223944_create_registration_override_tables', 105);
INSERT INTO public.migrations VALUES (75, '2025_01_10_create_registrations_table', 106);
INSERT INTO public.migrations VALUES (76, '2025_01_11_create_advisor_assignments_table', 107);
INSERT INTO public.migrations VALUES (77, '2025_09_08_165332_create_requirement_categories_table', 108);
INSERT INTO public.migrations VALUES (78, '2025_09_08_165310_create_degree_requirements_tables', 109);
INSERT INTO public.migrations VALUES (79, '2025_09_08_165321_create_program_requirements_tables', 110);
INSERT INTO public.migrations VALUES (80, '2025_09_08_165342_create_student_degree_progress_table', 111);
INSERT INTO public.migrations VALUES (81, '2025_09_08_165352_create_degree_audit_reports_table', 112);
INSERT INTO public.migrations VALUES (82, '2025_09_08_165402_create_academic_plans_table', 113);
INSERT INTO public.migrations VALUES (83, '2025_09_08_165412_create_plan_courses_table', 114);
INSERT INTO public.migrations VALUES (84, '2025_01_11_001_create_admissions_tables', 115);
INSERT INTO public.migrations VALUES (85, '2025_01_11_002_create_entrance_exam_tables', 116);
INSERT INTO public.migrations VALUES (86, '2025_01_11_003_create_missing_admission_tables', 117);
INSERT INTO public.migrations VALUES (87, '2025_01_14_fix_admissions_module', 118);
INSERT INTO public.migrations VALUES (88, '2025_09_15_130620_create_unified_documents_tables', 119);
INSERT INTO public.migrations VALUES (89, '2025_09_16_fix_database_schema', 120);
INSERT INTO public.migrations VALUES (90, '2025_09_16_add_missing_columns', 121);
INSERT INTO public.migrations VALUES (91, '2025_09_17_141241_add_user_lifecycle_management', 122);
INSERT INTO public.migrations VALUES (92, '2025_09_17_145305_fix_gender_constraint_safely', 123);
INSERT INTO public.migrations VALUES (93, '2025_09_17_152824_add_is_active_to_permissions_table', 124);
INSERT INTO public.migrations VALUES (94, '2025_09_19_005710_create_notifications_table', 125);
INSERT INTO public.migrations VALUES (95, '2025_09_20_000000_fix_missing_database_columns', 126);
INSERT INTO public.migrations VALUES (96, '2025_09_23_024236_add_missing_columns_to_application_documents_table', 127);
INSERT INTO public.migrations VALUES (97, '2025_09_23_034931_add_documents_column_to_admission_applications_table', 128);
INSERT INTO public.migrations VALUES (98, '2025_09_23_104905_add_file_hash_to_application_documents_table', 129);
INSERT INTO public.migrations VALUES (99, '2025_09_24_221811_add_custom_requirements_to_admission_applications_table', 130);
INSERT INTO public.migrations VALUES (100, '2025_09_25_113932_cleanup_admission_application_json_data', 131);


--
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: office_appointments; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: office_hours; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: organizational_permissions; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.organizational_permissions VALUES (1, 1, 'college', 6, 'college.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (2, 3, 'college', 7, 'college.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (3, 5, 'college', 8, 'college.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (4, 7, 'college', 9, 'college.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (5, 12, 'department', 5, 'department.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (6, 13, 'department', 6, 'department.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (7, 14, 'department', 7, 'department.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (8, 15, 'department', 8, 'department.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (9, 16, 'department', 9, 'department.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (10, 17, 'department', 10, 'department.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (11, 18, 'department', 11, 'department.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.organizational_permissions VALUES (12, 11, 'department', 4, 'department.manage', 'manage', NULL, '2025-09-04 22:28:59', NULL, true, NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');


--
-- Data for Name: override_approval_routes; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.override_approval_routes VALUES (1, 'credit_overload', 'advisor', 1, 10, '{"min_gpa":3.5,"max_credits":21}', 3, 'registrar', true, '2025-09-07 22:53:15', '2025-09-07 22:53:15');
INSERT INTO public.override_approval_routes VALUES (2, 'prerequisite', 'department_head', 1, 10, NULL, 5, 'registrar', true, '2025-09-07 22:53:15', '2025-09-07 22:53:15');
INSERT INTO public.override_approval_routes VALUES (3, 'capacity', 'registrar', 1, 10, '{"graduating_senior":true}', 2, NULL, true, '2025-09-07 22:53:15', '2025-09-07 22:53:15');
INSERT INTO public.override_approval_routes VALUES (4, 'time_conflict', 'registrar', 1, 10, NULL, 3, NULL, true, '2025-09-07 22:53:15', '2025-09-07 22:53:15');
INSERT INTO public.override_approval_routes VALUES (5, 'late_registration', 'registrar', 1, 10, NULL, 1, NULL, true, '2025-09-07 22:53:15', '2025-09-07 22:53:15');


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: payment_allocations; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: payment_gateways; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.payment_gateways VALUES (1, 'Stripe', 'stripe', NULL, NULL, NULL, true, true, '{"webhook_url":"\/webhook\/stripe","supported_cards":["visa","mastercard","amex","discover"],"supported_countries":["US","CA","GB","AU"]}', '["USD","EUR","GBP","CAD"]', 2.90, 0.30, '2025-09-07 20:35:43', '2025-09-07 20:35:43');
INSERT INTO public.payment_gateways VALUES (2, 'PayPal', 'paypal', NULL, NULL, NULL, false, true, '{"webhook_url":"\/webhook\/paypal","button_style":"gold"}', '["USD","EUR","GBP","CAD","AUD"]', 3.49, 0.49, '2025-09-07 20:35:43', '2025-09-07 20:35:43');
INSERT INTO public.payment_gateways VALUES (3, 'Stripe', 'stripe', NULL, NULL, NULL, true, true, '{"webhook_url":"\/webhook\/stripe","supported_cards":["visa","mastercard","amex","discover"],"supported_countries":["US","CA","GB","AU"]}', '["USD","EUR","GBP","CAD"]', 2.90, 0.30, '2025-09-07 20:52:27', '2025-09-07 20:52:27');
INSERT INTO public.payment_gateways VALUES (4, 'PayPal', 'paypal', NULL, NULL, NULL, false, true, '{"webhook_url":"\/webhook\/paypal","button_style":"gold"}', '["USD","EUR","GBP","CAD","AUD"]', 3.49, 0.49, '2025-09-07 20:52:28', '2025-09-07 20:52:28');


--
-- Data for Name: payment_gateway_transactions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: payment_plans; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: payment_plan_schedules; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.permissions VALUES (1, 'View Dashboard', 'dashboard.view', 'dashboard', 'View dashboard and analytics', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (2, 'Manage Dashboard', 'dashboard.manage', 'dashboard', 'Manage dashboard widgets and settings', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (3, 'View Users', 'users.view', 'users', 'View user list and details', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (4, 'Create Users', 'users.create', 'users', 'Create new users', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (5, 'Update Users', 'users.update', 'users', 'Update user information', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (6, 'Delete Users', 'users.delete', 'users', 'Delete users', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (7, 'Manage Users', 'users.manage', 'users', 'Full user management access', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (8, 'Assign roles Users', 'users.assign_roles', 'users', 'Assign roles to users', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (9, 'Manage permissions Users', 'users.manage_permissions', 'users', 'Manage user permissions', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (10, 'Import Users', 'users.import', 'users', 'Import users from files', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (11, 'Export Users', 'users.export', 'users', 'Export user data', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (12, 'View Students', 'students.view', 'students', 'View student records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (13, 'Create Students', 'students.create', 'students', 'Create new student records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (14, 'Update Students', 'students.update', 'students', 'Update student information', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (15, 'Delete Students', 'students.delete', 'students', 'Delete student records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (16, 'Manage Students', 'students.manage', 'students', 'Full student management access', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (17, 'View grades Students', 'students.view_grades', 'students', 'View student grades', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (18, 'Update grades Students', 'students.update_grades', 'students', 'Update student grades', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (19, 'View attendance Students', 'students.view_attendance', 'students', 'View student attendance', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (20, 'Update attendance Students', 'students.update_attendance', 'students', 'Update student attendance', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (21, 'View financial Students', 'students.view_financial', 'students', 'View student financial information', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (22, 'Manage enrollment Students', 'students.manage_enrollment', 'students', 'Manage student enrollment', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (23, 'Import Students', 'students.import', 'students', 'Import student data', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (24, 'Export Students', 'students.export', 'students', 'Export student data', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (25, 'View Faculty', 'faculty.view', 'faculty', 'View faculty records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (26, 'Create Faculty', 'faculty.create', 'faculty', 'Create new faculty records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (27, 'Update Faculty', 'faculty.update', 'faculty', 'Update faculty information', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (28, 'Delete Faculty', 'faculty.delete', 'faculty', 'Delete faculty records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (29, 'Manage Faculty', 'faculty.manage', 'faculty', 'Full faculty management access', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (30, 'Assign courses Faculty', 'faculty.assign_courses', 'faculty', 'Assign courses to faculty', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (31, 'View schedule Faculty', 'faculty.view_schedule', 'faculty', 'View faculty schedules', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (32, 'Manage schedule Faculty', 'faculty.manage_schedule', 'faculty', 'Manage faculty schedules', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (33, 'View evaluations Faculty', 'faculty.view_evaluations', 'faculty', 'View faculty evaluations', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (34, 'Manage evaluations Faculty', 'faculty.manage_evaluations', 'faculty', 'Manage faculty evaluations', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (35, 'View Courses', 'courses.view', 'courses', 'View course catalog', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (36, 'Create Courses', 'courses.create', 'courses', 'Create new courses', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (37, 'Update Courses', 'courses.update', 'courses', 'Update course information', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (38, 'Delete Courses', 'courses.delete', 'courses', 'Delete courses', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (39, 'Manage Courses', 'courses.manage', 'courses', 'Full course management access', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (40, 'Assign faculty Courses', 'courses.assign_faculty', 'courses', 'Assign faculty to courses', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (41, 'Manage prerequisites Courses', 'courses.manage_prerequisites', 'courses', 'Manage course prerequisites', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (42, 'Manage sections Courses', 'courses.manage_sections', 'courses', 'Manage course sections', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (43, 'View enrollments Courses', 'courses.view_enrollments', 'courses', 'View course enrollments', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (44, 'Manage enrollments Courses', 'courses.manage_enrollments', 'courses', 'Manage course enrollments', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (45, 'View Enrollment', 'enrollment.view', 'enrollment', 'View enrollment records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (46, 'Create Enrollment', 'enrollment.create', 'enrollment', 'Create new enrollments', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (47, 'Update Enrollment', 'enrollment.update', 'enrollment', 'Update enrollment information', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (48, 'Delete Enrollment', 'enrollment.delete', 'enrollment', 'Delete enrollments', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (49, 'Manage Enrollment', 'enrollment.manage', 'enrollment', 'Full enrollment management access', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (50, 'Approve Enrollment', 'enrollment.approve', 'enrollment', 'Approve enrollment requests', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (51, 'Register students Enrollment', 'enrollment.register_students', 'enrollment', 'Register students for courses', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (52, 'Drop students Enrollment', 'enrollment.drop_students', 'enrollment', 'Drop students from courses', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (53, 'Manage waitlist Enrollment', 'enrollment.manage_waitlist', 'enrollment', 'Manage course waitlists', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (54, 'Override prerequisites Enrollment', 'enrollment.override_prerequisites', 'enrollment', 'Override course prerequisites', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (55, 'View Grades', 'grades.view', 'grades', 'View grades', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (56, 'Create Grades', 'grades.create', 'grades', 'Enter new grades', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (57, 'Update Grades', 'grades.update', 'grades', 'Update existing grades', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (58, 'Delete Grades', 'grades.delete', 'grades', 'Delete grades', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (59, 'Manage Grades', 'grades.manage', 'grades', 'Full grade management access', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (60, 'Approve Grades', 'grades.approve', 'grades', 'Approve final grades', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (61, 'Generate transcripts Grades', 'grades.generate_transcripts', 'grades', 'Generate transcripts', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (62, 'Calculate gpa Grades', 'grades.calculate_gpa', 'grades', 'Calculate GPA', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (63, 'Manage grading scales Grades', 'grades.manage_grading_scales', 'grades', 'Manage grading scales', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (64, 'View Attendance', 'attendance.view', 'attendance', 'View attendance records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (65, 'Create Attendance', 'attendance.create', 'attendance', 'Mark attendance', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (66, 'Update Attendance', 'attendance.update', 'attendance', 'Update attendance records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (67, 'Delete Attendance', 'attendance.delete', 'attendance', 'Delete attendance records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (68, 'Manage Attendance', 'attendance.manage', 'attendance', 'Full attendance management access', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (69, 'Generate reports Attendance', 'attendance.generate_reports', 'attendance', 'Generate attendance reports', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (70, 'Manage policies Attendance', 'attendance.manage_policies', 'attendance', 'Manage attendance policies', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (71, 'View Finance', 'finance.view', 'finance', 'View financial records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (72, 'Create Finance', 'finance.create', 'finance', 'Create financial transactions', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (73, 'Update Finance', 'finance.update', 'finance', 'Update financial records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (74, 'Delete Finance', 'finance.delete', 'finance', 'Delete financial records', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (75, 'Manage Finance', 'finance.manage', 'finance', 'Full financial management access', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (76, 'Manage fees Finance', 'finance.manage_fees', 'finance', 'Manage fee structure', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (77, 'Process payments Finance', 'finance.process_payments', 'finance', 'Process payments', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (78, 'Generate invoices Finance', 'finance.generate_invoices', 'finance', 'Generate invoices', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (79, 'Manage scholarships Finance', 'finance.manage_scholarships', 'finance', 'Manage scholarships', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (80, 'View reports Finance', 'finance.view_reports', 'finance', 'View financial reports', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (81, 'Generate reports Finance', 'finance.generate_reports', 'finance', 'Generate financial reports', true, '{}', '2025-08-24 16:54:52', '2025-08-24 16:54:52', NULL, true);
INSERT INTO public.permissions VALUES (82, 'Manage refunds Finance', 'finance.manage_refunds', 'finance', 'Process refunds', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (83, 'View Library', 'library.view', 'library', 'View library resources', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (84, 'Create Library', 'library.create', 'library', 'Add new library resources', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (85, 'Update Library', 'library.update', 'library', 'Update library resources', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (86, 'Delete Library', 'library.delete', 'library', 'Delete library resources', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (87, 'Manage Library', 'library.manage', 'library', 'Full library management access', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (88, 'Issue books Library', 'library.issue_books', 'library', 'Issue books to users', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (89, 'Return books Library', 'library.return_books', 'library', 'Process book returns', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (90, 'Manage fines Library', 'library.manage_fines', 'library', 'Manage library fines', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (91, 'Manage reservations Library', 'library.manage_reservations', 'library', 'Manage book reservations', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (92, 'View Hostel', 'hostel.view', 'hostel', 'View hostel information', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (93, 'Create Hostel', 'hostel.create', 'hostel', 'Create hostel records', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (94, 'Update Hostel', 'hostel.update', 'hostel', 'Update hostel information', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (95, 'Delete Hostel', 'hostel.delete', 'hostel', 'Delete hostel records', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (96, 'Manage Hostel', 'hostel.manage', 'hostel', 'Full hostel management access', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (97, 'Allocate rooms Hostel', 'hostel.allocate_rooms', 'hostel', 'Allocate rooms to students', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (98, 'Manage facilities Hostel', 'hostel.manage_facilities', 'hostel', 'Manage hostel facilities', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (99, 'Manage complaints Hostel', 'hostel.manage_complaints', 'hostel', 'Handle hostel complaints', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (100, 'Manage fees Hostel', 'hostel.manage_fees', 'hostel', 'Manage hostel fees', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (101, 'View Transport', 'transport.view', 'transport', 'View transport information', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (102, 'Create Transport', 'transport.create', 'transport', 'Create transport records', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (103, 'Update Transport', 'transport.update', 'transport', 'Update transport information', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (104, 'Delete Transport', 'transport.delete', 'transport', 'Delete transport records', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (105, 'Manage Transport', 'transport.manage', 'transport', 'Full transport management access', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (106, 'Manage routes Transport', 'transport.manage_routes', 'transport', 'Manage transport routes', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (107, 'Manage vehicles Transport', 'transport.manage_vehicles', 'transport', 'Manage vehicles', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (108, 'Assign drivers Transport', 'transport.assign_drivers', 'transport', 'Assign drivers to routes', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (109, 'Manage fees Transport', 'transport.manage_fees', 'transport', 'Manage transport fees', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (110, 'View Examinations', 'examinations.view', 'examinations', 'View examination schedules', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (111, 'Create Examinations', 'examinations.create', 'examinations', 'Create examination schedules', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (112, 'Update Examinations', 'examinations.update', 'examinations', 'Update examination information', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (113, 'Delete Examinations', 'examinations.delete', 'examinations', 'Delete examination records', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (114, 'Manage Examinations', 'examinations.manage', 'examinations', 'Full examination management access', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (115, 'Schedule exams Examinations', 'examinations.schedule_exams', 'examinations', 'Schedule examinations', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (116, 'Assign invigilators Examinations', 'examinations.assign_invigilators', 'examinations', 'Assign exam invigilators', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (117, 'Manage venues Examinations', 'examinations.manage_venues', 'examinations', 'Manage examination venues', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (118, 'Generate halltickets Examinations', 'examinations.generate_halltickets', 'examinations', 'Generate hall tickets', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (119, 'Manage results Examinations', 'examinations.manage_results', 'examinations', 'Manage examination results', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (120, 'View academic Reports', 'reports.view_academic', 'reports', 'View academic reports', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (121, 'View financial Reports', 'reports.view_financial', 'reports', 'View financial reports', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (122, 'View administrative Reports', 'reports.view_administrative', 'reports', 'View administrative reports', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (123, 'Generate custom Reports', 'reports.generate_custom', 'reports', 'Generate custom reports', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (124, 'Export Reports', 'reports.export', 'reports', 'Export reports', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (125, 'Schedule Reports', 'reports.schedule', 'reports', 'Schedule automated reports', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (126, 'View Settings', 'settings.view', 'settings', 'View system settings', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (127, 'Update Settings', 'settings.update', 'settings', 'Update system settings', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (128, 'Manage academic Settings', 'settings.manage_academic', 'settings', 'Manage academic settings', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (129, 'Manage system Settings', 'settings.manage_system', 'settings', 'Manage system configuration', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (130, 'Manage security Settings', 'settings.manage_security', 'settings', 'Manage security settings', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (131, 'Manage backup Settings', 'settings.manage_backup', 'settings', 'Manage system backups', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (132, 'Manage integration Settings', 'settings.manage_integration', 'settings', 'Manage third-party integrations', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (133, 'View logs Audit', 'audit.view_logs', 'audit', 'View audit logs', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (134, 'Export logs Audit', 'audit.export_logs', 'audit', 'Export audit logs', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (135, 'Manage retention Audit', 'audit.manage_retention', 'audit', 'Manage log retention policies', true, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL, true);
INSERT INTO public.permissions VALUES (136, 'Application Create', 'application.create', 'application', 'Applicant permission: application.create', false, '{}', '2025-09-17 15:37:51', '2025-09-17 15:37:51', NULL, true);
INSERT INTO public.permissions VALUES (137, 'Application View Own', 'application.view.own', 'application', 'Applicant permission: application.view.own', false, '{}', '2025-09-17 15:37:52', '2025-09-17 15:37:52', NULL, true);
INSERT INTO public.permissions VALUES (138, 'Application Edit Own', 'application.edit.own', 'application', 'Applicant permission: application.edit.own', false, '{}', '2025-09-17 15:37:52', '2025-09-17 15:37:52', NULL, true);
INSERT INTO public.permissions VALUES (139, 'Application Submit Own', 'application.submit.own', 'application', 'Applicant permission: application.submit.own', false, '{}', '2025-09-17 15:37:52', '2025-09-17 15:37:52', NULL, true);
INSERT INTO public.permissions VALUES (140, 'Application Withdraw Own', 'application.withdraw.own', 'application', 'Applicant permission: application.withdraw.own', false, '{}', '2025-09-17 15:37:52', '2025-09-17 15:37:52', NULL, true);
INSERT INTO public.permissions VALUES (141, 'Application Track Own', 'application.track.own', 'application', 'Applicant permission: application.track.own', false, '{}', '2025-09-17 15:37:52', '2025-09-17 15:37:52', NULL, true);
INSERT INTO public.permissions VALUES (142, 'Document Upload Own', 'document.upload.own', 'document', 'Applicant permission: document.upload.own', false, '{}', '2025-09-17 15:37:52', '2025-09-17 15:37:52', NULL, true);
INSERT INTO public.permissions VALUES (143, 'Document View Own', 'document.view.own', 'document', 'Applicant permission: document.view.own', false, '{}', '2025-09-17 15:37:52', '2025-09-17 15:37:52', NULL, true);
INSERT INTO public.permissions VALUES (144, 'Payment Make Own', 'payment.make.own', 'payment', 'Applicant permission: payment.make.own', false, '{}', '2025-09-17 15:37:52', '2025-09-17 15:37:52', NULL, true);
INSERT INTO public.permissions VALUES (145, 'Exam Register', 'exam.register', 'exam', 'Applicant permission: exam.register', false, '{}', '2025-09-17 15:37:52', '2025-09-17 15:37:52', NULL, true);
INSERT INTO public.permissions VALUES (146, 'Exam View Own', 'exam.view.own', 'exam', 'Applicant permission: exam.view.own', false, '{}', '2025-09-17 15:37:52', '2025-09-17 15:37:52', NULL, true);


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.roles VALUES (1, 'Super Administrator', 'super-administrator', 'Full system access with all permissions. Can manage system configuration, users, and all modules.', true, true, 1, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (2, 'System Administrator', 'system-administrator', 'Technical administration including system settings, backups, and integrations.', true, true, 2, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (3, 'Academic Administrator', 'academic-administrator', 'Manages academic operations including courses, faculty, and student records.', true, true, 3, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (4, 'Financial Administrator', 'financial-administrator', 'Manages all financial operations including fees, payments, and financial reports.', true, true, 4, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (5, 'Registrar', 'registrar', 'Manages student registration, enrollment, and academic records.', true, true, 5, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (6, 'Dean', 'dean', 'Academic oversight with access to faculty and student performance data.', true, true, 6, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (7, 'Department Head', 'department-head', 'Manages department-specific courses, faculty, and students.', true, true, 7, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (8, 'Faculty', 'faculty', 'Teaching staff with access to courses, grades, and attendance.', true, true, 8, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (9, 'Advisor', 'advisor', 'Student advisor with access to advisee records and academic planning.', true, true, 9, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (10, 'Staff', 'staff', 'Administrative staff with limited access to specific modules.', true, true, 10, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (11, 'Student', 'student', 'Students with access to their own records and services.', true, true, 11, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (12, 'Parent/Guardian', 'parent-guardian', 'Limited view access to linked student records.', true, true, 12, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (13, 'Auditor', 'auditor', 'Read-only access to all modules for audit purposes.', true, true, 13, '{}', '2025-08-24 16:54:53', '2025-08-24 16:54:53', NULL);
INSERT INTO public.roles VALUES (17, 'admissions_officer', 'admissions-officer', 'Can manage applications', false, true, 50, '"{\"module\":\"admissions\"}"', '2025-09-12 15:49:14', '2025-09-12 15:49:14', NULL);
INSERT INTO public.roles VALUES (18, 'admissions_director', 'admissions-director', 'Can make admission decisions', false, true, 40, '"{\"module\":\"admissions\"}"', '2025-09-12 15:49:14', '2025-09-12 15:49:14', NULL);
INSERT INTO public.roles VALUES (19, 'Applicant', 'applicant', 'Admission applicant role', false, true, 999, '{}', '2025-09-17 15:05:07', '2025-09-17 15:05:07', NULL);
INSERT INTO public.roles VALUES (20, 'Alumni', 'alumni', 'Graduated student', false, true, 51, '{}', '2025-09-17 15:21:14', '2025-09-17 15:21:14', NULL);
INSERT INTO public.roles VALUES (21, 'Guest', 'guest', 'Guest user with limited access', false, true, 70, '{}', '2025-09-17 15:21:14', '2025-09-17 15:21:14', NULL);


--
-- Data for Name: permission_role; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.permission_role VALUES (1, 136, 19, NULL, NULL, '2025-09-17 15:37:51', '2025-09-17 15:37:51');
INSERT INTO public.permission_role VALUES (2, 137, 19, NULL, NULL, '2025-09-17 15:37:52', '2025-09-17 15:37:52');
INSERT INTO public.permission_role VALUES (3, 138, 19, NULL, NULL, '2025-09-17 15:37:52', '2025-09-17 15:37:52');
INSERT INTO public.permission_role VALUES (4, 139, 19, NULL, NULL, '2025-09-17 15:37:52', '2025-09-17 15:37:52');
INSERT INTO public.permission_role VALUES (5, 140, 19, NULL, NULL, '2025-09-17 15:37:52', '2025-09-17 15:37:52');
INSERT INTO public.permission_role VALUES (6, 141, 19, NULL, NULL, '2025-09-17 15:37:52', '2025-09-17 15:37:52');
INSERT INTO public.permission_role VALUES (7, 142, 19, NULL, NULL, '2025-09-17 15:37:52', '2025-09-17 15:37:52');
INSERT INTO public.permission_role VALUES (8, 143, 19, NULL, NULL, '2025-09-17 15:37:52', '2025-09-17 15:37:52');
INSERT INTO public.permission_role VALUES (9, 144, 19, NULL, NULL, '2025-09-17 15:37:52', '2025-09-17 15:37:52');
INSERT INTO public.permission_role VALUES (10, 145, 19, NULL, NULL, '2025-09-17 15:37:52', '2025-09-17 15:37:52');
INSERT INTO public.permission_role VALUES (11, 146, 19, NULL, NULL, '2025-09-17 15:37:52', '2025-09-17 15:37:52');
INSERT INTO public.permission_role VALUES (12, 1, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (13, 2, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (14, 3, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (15, 4, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (16, 5, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (17, 6, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (18, 7, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (19, 8, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (20, 9, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (21, 10, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (22, 11, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (23, 12, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (24, 13, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (25, 14, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (26, 15, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (27, 16, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (28, 17, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (29, 18, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (30, 19, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (31, 20, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (32, 21, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (33, 22, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (34, 23, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (35, 24, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (36, 25, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (37, 26, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (38, 27, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (39, 28, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (40, 29, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (41, 30, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (42, 31, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (43, 32, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (44, 33, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (45, 34, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (46, 35, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (47, 36, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (48, 37, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (49, 38, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (50, 39, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (51, 40, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (52, 41, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (53, 42, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (54, 43, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (55, 44, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (56, 45, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (57, 46, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (58, 47, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (59, 48, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (60, 49, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (61, 50, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (62, 51, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (63, 52, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (64, 53, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (65, 54, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (66, 55, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (67, 56, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (68, 57, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (69, 58, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (70, 59, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (71, 60, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (72, 61, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (73, 62, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (74, 63, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (75, 64, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (76, 65, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (77, 66, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (78, 67, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (79, 68, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (80, 69, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (81, 70, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (82, 71, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (83, 72, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (84, 73, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (85, 74, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (86, 75, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (87, 76, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (88, 77, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (89, 78, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (90, 79, 1, NULL, NULL, '2025-09-19 15:26:03', '2025-09-19 15:26:03');
INSERT INTO public.permission_role VALUES (91, 80, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (92, 81, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (93, 82, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (94, 83, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (95, 84, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (96, 85, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (97, 86, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (98, 87, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (99, 88, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (100, 89, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (101, 90, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (102, 91, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (103, 92, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (104, 93, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (105, 94, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (106, 95, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (107, 96, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (108, 97, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (109, 98, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (110, 99, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (111, 100, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (112, 101, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (113, 102, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (114, 103, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (115, 104, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (116, 105, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (117, 106, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (118, 107, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (119, 108, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (120, 109, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (121, 110, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (122, 111, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (123, 112, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (124, 113, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (125, 114, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (126, 115, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (127, 116, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (128, 117, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (129, 118, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (130, 119, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (131, 120, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (132, 121, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (133, 122, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (134, 123, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (135, 124, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (136, 125, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (137, 126, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (138, 127, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (139, 128, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (140, 129, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (141, 130, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (142, 131, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (143, 132, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (144, 133, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (145, 134, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (146, 135, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (147, 136, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (148, 137, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (149, 138, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (150, 139, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (151, 140, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (152, 141, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (153, 142, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (154, 143, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (155, 144, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (156, 145, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (157, 146, 1, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (158, 1, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (159, 2, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (160, 3, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (161, 4, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (162, 5, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (163, 6, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (164, 7, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (165, 8, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (166, 9, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (167, 10, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (168, 11, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (169, 12, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (170, 13, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (171, 14, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (172, 15, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (173, 16, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (174, 17, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (175, 18, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (176, 19, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (177, 20, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (178, 21, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (179, 22, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (180, 23, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (181, 24, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (182, 25, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (183, 26, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (184, 27, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (185, 28, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (186, 29, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (187, 30, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (188, 31, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (189, 32, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (190, 33, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (191, 34, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (192, 35, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (193, 36, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (194, 37, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (195, 38, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (196, 39, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (197, 40, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (198, 41, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (199, 42, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (200, 43, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (201, 44, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (202, 45, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (203, 46, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (204, 47, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (205, 48, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (206, 49, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (207, 50, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (208, 51, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (209, 52, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (210, 53, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (211, 54, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (212, 55, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (213, 56, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (214, 57, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (215, 58, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (216, 59, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (217, 60, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (218, 61, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (219, 62, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (220, 63, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (221, 64, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (222, 65, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (223, 66, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (224, 67, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (225, 68, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (226, 69, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (227, 70, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (228, 71, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (229, 72, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (230, 73, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (231, 74, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (232, 75, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (233, 76, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (234, 77, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (235, 78, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (236, 79, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (237, 80, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (238, 81, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (239, 82, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (240, 83, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (241, 84, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (242, 85, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (243, 86, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (244, 87, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (245, 88, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (246, 89, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (247, 90, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (248, 91, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (249, 92, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (250, 93, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (251, 94, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (252, 95, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (253, 96, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (254, 97, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (255, 98, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (256, 99, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (257, 100, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (258, 101, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (259, 102, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (260, 103, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (261, 104, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (262, 105, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (263, 106, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (264, 107, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (265, 108, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (266, 109, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (267, 110, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (268, 111, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (269, 112, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (270, 113, 2, NULL, NULL, '2025-09-19 15:26:04', '2025-09-19 15:26:04');
INSERT INTO public.permission_role VALUES (271, 114, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (272, 115, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (273, 116, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (274, 117, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (275, 118, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (276, 119, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (277, 120, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (278, 121, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (279, 122, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (280, 123, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (281, 124, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (282, 125, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (283, 126, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (284, 127, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (285, 128, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (286, 129, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (287, 130, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (288, 131, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (289, 132, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (290, 133, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (291, 134, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (292, 135, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (293, 136, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (294, 137, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (295, 138, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (296, 139, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (297, 140, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (298, 141, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (299, 142, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (300, 143, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (301, 144, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (302, 145, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');
INSERT INTO public.permission_role VALUES (303, 146, 2, NULL, NULL, '2025-09-19 15:26:05', '2025-09-19 15:26:05');


--
-- Data for Name: permission_user; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: plan_terms; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: plan_courses; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: prerequisite_overrides; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: prerequisite_waivers; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: program_courses; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: program_prerequisites; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: program_requirements; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.program_requirements VALUES (17, 1, 17, '2025-2026', NULL, 6.0, 2, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (18, 1, 18, '2025-2026', NULL, NULL, NULL, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (19, 1, 19, '2025-2026', NULL, 8.0, NULL, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (20, 1, 20, '2025-2026', NULL, 9.0, NULL, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (21, 1, 21, '2025-2026', NULL, 9.0, NULL, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (22, 1, 22, '2025-2026', NULL, 6.0, 2, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (23, 1, 23, '2025-2026', NULL, 9.0, 3, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (24, 1, 24, '2025-2026', NULL, 6.0, 2, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (25, 1, 25, '2025-2026', NULL, 6.0, 2, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (26, 1, 26, '2025-2026', NULL, 3.0, 1, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (27, 1, 27, '2025-2026', NULL, 12.0, NULL, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (28, 1, 28, '2025-2026', NULL, 15.0, NULL, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (29, 1, 29, '2025-2026', NULL, 120.0, NULL, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (30, 1, 30, '2025-2026', NULL, NULL, NULL, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (31, 1, 31, '2025-2026', NULL, NULL, NULL, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');
INSERT INTO public.program_requirements VALUES (32, 1, 32, '2025-2026', NULL, NULL, NULL, 'all', NULL, true, '2025-09-09 12:18:20', '2025-09-09 12:18:20');


--
-- Data for Name: program_types; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.program_types VALUES (1, 'Certificate', 'CERT', 0, NULL, true, '2025-09-16 11:16:32', '2025-09-16 11:16:32');
INSERT INTO public.program_types VALUES (2, 'Diploma', 'DIP', 1, NULL, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.program_types VALUES (3, 'Undergraduate', 'UG', 2, NULL, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.program_types VALUES (4, 'Graduate', 'GR', 3, NULL, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.program_types VALUES (5, 'Doctoral', 'PHD', 4, NULL, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');
INSERT INTO public.program_types VALUES (6, 'Professional', 'PROF', 3, NULL, true, '2025-09-16 11:16:33', '2025-09-16 11:16:33');


--
-- Data for Name: quizzes; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: quiz_attempts; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: quiz_questions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: recommendation_letters; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: refunds; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: registration_carts; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.registration_carts VALUES (1, 11, 1, '[1,2,3]', 9, NULL, false, false, NULL, 'active', '2025-09-02 16:52:03', '2025-09-02 16:52:03');
INSERT INTO public.registration_carts VALUES (2, 1, 1, '[1,2,3]', 12, NULL, false, false, NULL, 'active', '2025-09-08 13:48:17', '2025-09-08 13:48:17');


--
-- Data for Name: registration_configurations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.registration_configurations VALUES (1, true, 7, true, false, true, 10, 2, 10, 50.00, NULL, '2025-09-07 09:58:49', '2025-09-07 09:58:49');


--
-- Data for Name: registration_holds; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: registration_logs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: registration_override_requests; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: registration_overrides; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: registration_periods; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.registration_periods VALUES (1, 1, 'priority', '2025-08-19', '2025-08-21', '["senior","graduate"]', '["athlete","honors","disability"]', false, 'Priority registration for seniors, athletes, and honors students', '2025-08-26 07:57:23', '2025-08-26 07:57:23');
INSERT INTO public.registration_periods VALUES (2, 1, 'regular', '2025-08-22', '2025-09-25', '["senior","junior","sophomore","freshman"]', NULL, true, 'Regular registration period for all students', '2025-08-26 07:57:23', '2025-08-26 07:57:23');
INSERT INTO public.registration_periods VALUES (3, 1, 'late', '2025-09-26', '2025-10-10', NULL, NULL, false, 'Late registration with additional fees', '2025-08-26 07:57:23', '2025-08-26 07:57:23');


--
-- Data for Name: registrations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.registrations VALUES (1, 1, 1, 1, '2025-09-08 13:48:39', 'pending', 'regular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-08 13:48:39', '2025-09-08 13:48:39');
INSERT INTO public.registrations VALUES (2, 11, 1, 1, '2025-09-08 14:09:13', 'enrolled', 'regular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-08 14:09:13', '2025-09-08 14:09:13');
INSERT INTO public.registrations VALUES (3, 11, 2, 1, '2025-09-08 14:09:13', 'enrolled', 'regular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-08 14:09:13', '2025-09-08 14:09:13');
INSERT INTO public.registrations VALUES (4, 11, 3, 1, '2025-09-08 14:09:13', 'enrolled', 'regular', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-08 14:09:13', '2025-09-08 14:09:13');


--
-- Data for Name: requirement_substitutions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: role_user; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.role_user VALUES (1, 6, 1, '2025-09-04 22:28:48', NULL, NULL, true, '2025-09-04 22:28:49', '2025-09-04 22:28:49');
INSERT INTO public.role_user VALUES (2, 6, 2, '2025-09-04 22:28:49', NULL, NULL, true, '2025-09-04 22:28:49', '2025-09-04 22:28:49');
INSERT INTO public.role_user VALUES (3, 6, 3, '2025-09-04 22:28:49', NULL, NULL, true, '2025-09-04 22:28:49', '2025-09-04 22:28:49');
INSERT INTO public.role_user VALUES (4, 6, 4, '2025-09-04 22:28:49', NULL, NULL, true, '2025-09-04 22:28:49', '2025-09-04 22:28:49');
INSERT INTO public.role_user VALUES (5, 6, 5, '2025-09-04 22:28:49', NULL, NULL, true, '2025-09-04 22:28:49', '2025-09-04 22:28:49');
INSERT INTO public.role_user VALUES (6, 6, 6, '2025-09-04 22:28:50', NULL, NULL, true, '2025-09-04 22:28:50', '2025-09-04 22:28:50');
INSERT INTO public.role_user VALUES (7, 6, 7, '2025-09-04 22:28:50', NULL, NULL, true, '2025-09-04 22:28:50', '2025-09-04 22:28:50');
INSERT INTO public.role_user VALUES (8, 6, 8, '2025-09-04 22:28:50', NULL, NULL, true, '2025-09-04 22:28:50', '2025-09-04 22:28:50');
INSERT INTO public.role_user VALUES (9, 7, 9, '2025-09-04 22:28:50', NULL, NULL, true, '2025-09-04 22:28:50', '2025-09-04 22:28:50');
INSERT INTO public.role_user VALUES (10, 7, 10, '2025-09-04 22:28:51', NULL, NULL, true, '2025-09-04 22:28:51', '2025-09-04 22:28:51');
INSERT INTO public.role_user VALUES (11, 7, 11, '2025-09-04 22:28:51', NULL, NULL, true, '2025-09-04 22:28:51', '2025-09-04 22:28:51');
INSERT INTO public.role_user VALUES (12, 7, 12, '2025-09-04 22:28:51', NULL, NULL, true, '2025-09-04 22:28:51', '2025-09-04 22:28:51');
INSERT INTO public.role_user VALUES (13, 7, 13, '2025-09-04 22:28:51', NULL, NULL, true, '2025-09-04 22:28:51', '2025-09-04 22:28:51');
INSERT INTO public.role_user VALUES (14, 7, 14, '2025-09-04 22:28:51', NULL, NULL, true, '2025-09-04 22:28:51', '2025-09-04 22:28:51');
INSERT INTO public.role_user VALUES (15, 7, 15, '2025-09-04 22:28:52', NULL, NULL, true, '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.role_user VALUES (16, 7, 16, '2025-09-04 22:28:52', NULL, NULL, true, '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.role_user VALUES (17, 7, 17, '2025-09-04 22:28:52', NULL, NULL, true, '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.role_user VALUES (18, 7, 18, '2025-09-04 22:28:52', NULL, NULL, true, '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.role_user VALUES (19, 8, 19, '2025-09-04 22:28:52', NULL, NULL, true, '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.role_user VALUES (20, 8, 20, '2025-09-04 22:28:53', NULL, NULL, true, '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.role_user VALUES (21, 8, 21, '2025-09-04 22:28:53', NULL, NULL, true, '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.role_user VALUES (22, 8, 22, '2025-09-04 22:28:53', NULL, NULL, true, '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.role_user VALUES (23, 8, 23, '2025-09-04 22:28:53', NULL, NULL, true, '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.role_user VALUES (24, 8, 24, '2025-09-04 22:28:54', NULL, NULL, true, '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.role_user VALUES (25, 8, 25, '2025-09-04 22:28:54', NULL, NULL, true, '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.role_user VALUES (26, 8, 26, '2025-09-04 22:28:54', NULL, NULL, true, '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.role_user VALUES (27, 8, 27, '2025-09-04 22:28:54', NULL, NULL, true, '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.role_user VALUES (28, 8, 28, '2025-09-04 22:28:55', NULL, NULL, true, '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.role_user VALUES (29, 8, 29, '2025-09-04 22:28:55', NULL, NULL, true, '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.role_user VALUES (30, 8, 30, '2025-09-04 22:28:55', NULL, NULL, true, '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.role_user VALUES (31, 8, 31, '2025-09-04 22:28:55', NULL, NULL, true, '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.role_user VALUES (32, 8, 32, '2025-09-04 22:28:55', NULL, NULL, true, '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.role_user VALUES (33, 8, 33, '2025-09-04 22:28:56', NULL, NULL, true, '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.role_user VALUES (34, 8, 34, '2025-09-04 22:28:56', NULL, NULL, true, '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.role_user VALUES (35, 8, 35, '2025-09-04 22:28:56', NULL, NULL, true, '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.role_user VALUES (36, 8, 36, '2025-09-04 22:28:56', NULL, NULL, true, '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.role_user VALUES (37, 8, 37, '2025-09-04 22:28:56', NULL, NULL, true, '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.role_user VALUES (38, 8, 38, '2025-09-04 22:28:57', NULL, NULL, true, '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.role_user VALUES (39, 8, 39, '2025-09-04 22:28:57', NULL, NULL, true, '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.role_user VALUES (40, 8, 40, '2025-09-04 22:28:57', NULL, NULL, true, '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.role_user VALUES (41, 8, 41, '2025-09-04 22:28:57', NULL, NULL, true, '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.role_user VALUES (42, 8, 42, '2025-09-04 22:28:58', NULL, NULL, true, '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.role_user VALUES (43, 8, 43, '2025-09-04 22:28:58', NULL, NULL, true, '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.role_user VALUES (44, 8, 44, '2025-09-04 22:28:58', NULL, NULL, true, '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.role_user VALUES (45, 8, 45, '2025-09-04 22:28:58', NULL, NULL, true, '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.role_user VALUES (46, 8, 46, '2025-09-04 22:28:58', NULL, NULL, true, '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.role_user VALUES (47, 8, 47, '2025-09-04 22:28:59', NULL, NULL, true, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.role_user VALUES (48, 1, 48, NULL, NULL, NULL, false, '2025-09-05 14:55:05', '2025-09-05 14:55:05');
INSERT INTO public.role_user VALUES (49, 3, 49, NULL, NULL, NULL, false, '2025-09-05 15:45:16', '2025-09-05 15:45:16');
INSERT INTO public.role_user VALUES (50, 4, 50, NULL, NULL, NULL, false, '2025-09-05 15:45:30', '2025-09-05 15:45:30');
INSERT INTO public.role_user VALUES (51, 5, 51, NULL, NULL, NULL, false, '2025-09-05 15:45:45', '2025-09-05 15:45:45');
INSERT INTO public.role_user VALUES (52, 9, 52, NULL, NULL, NULL, false, '2025-09-05 15:46:14', '2025-09-05 15:46:14');
INSERT INTO public.role_user VALUES (53, 10, 53, NULL, NULL, NULL, false, '2025-09-05 15:46:41', '2025-09-05 15:46:41');
INSERT INTO public.role_user VALUES (54, 13, 54, NULL, NULL, NULL, false, '2025-09-05 15:47:07', '2025-09-05 15:47:07');
INSERT INTO public.role_user VALUES (55, 11, 55, '2025-09-05 23:47:25', NULL, NULL, true, '2025-09-05 23:47:25', '2025-09-05 23:47:25');
INSERT INTO public.role_user VALUES (56, 11, 56, '2025-09-05 23:47:26', NULL, NULL, true, '2025-09-05 23:47:26', '2025-09-05 23:47:26');
INSERT INTO public.role_user VALUES (57, 11, 57, '2025-09-05 23:47:26', NULL, NULL, true, '2025-09-05 23:47:26', '2025-09-05 23:47:26');
INSERT INTO public.role_user VALUES (58, 11, 58, '2025-09-05 23:47:26', NULL, NULL, true, '2025-09-05 23:47:26', '2025-09-05 23:47:26');
INSERT INTO public.role_user VALUES (59, 11, 59, '2025-09-05 23:47:27', NULL, NULL, true, '2025-09-05 23:47:27', '2025-09-05 23:47:27');
INSERT INTO public.role_user VALUES (60, 11, 60, '2025-09-05 23:47:27', NULL, NULL, true, '2025-09-05 23:47:27', '2025-09-05 23:47:27');
INSERT INTO public.role_user VALUES (61, 11, 61, '2025-09-05 23:47:27', NULL, NULL, true, '2025-09-05 23:47:27', '2025-09-05 23:47:27');
INSERT INTO public.role_user VALUES (62, 11, 62, '2025-09-05 23:47:27', NULL, NULL, true, '2025-09-05 23:47:27', '2025-09-05 23:47:27');
INSERT INTO public.role_user VALUES (63, 11, 63, '2025-09-05 23:47:27', NULL, NULL, true, '2025-09-05 23:47:27', '2025-09-05 23:47:27');
INSERT INTO public.role_user VALUES (64, 11, 64, '2025-09-05 23:47:28', NULL, NULL, true, '2025-09-05 23:47:28', '2025-09-05 23:47:28');
INSERT INTO public.role_user VALUES (65, 11, 65, '2025-09-05 23:47:28', NULL, NULL, true, '2025-09-05 23:47:28', '2025-09-05 23:47:28');
INSERT INTO public.role_user VALUES (66, 11, 66, '2025-09-05 23:47:28', NULL, NULL, true, '2025-09-05 23:47:28', '2025-09-05 23:47:28');
INSERT INTO public.role_user VALUES (67, 11, 67, '2025-09-05 23:47:28', NULL, NULL, true, '2025-09-05 23:47:28', '2025-09-05 23:47:28');
INSERT INTO public.role_user VALUES (68, 11, 68, '2025-09-05 23:47:29', NULL, NULL, true, '2025-09-05 23:47:29', '2025-09-05 23:47:29');
INSERT INTO public.role_user VALUES (69, 11, 69, '2025-09-05 23:47:29', NULL, NULL, true, '2025-09-05 23:47:29', '2025-09-05 23:47:29');
INSERT INTO public.role_user VALUES (70, 11, 70, '2025-09-05 23:47:29', NULL, NULL, true, '2025-09-05 23:47:29', '2025-09-05 23:47:29');
INSERT INTO public.role_user VALUES (71, 11, 71, '2025-09-05 23:47:29', NULL, NULL, true, '2025-09-05 23:47:29', '2025-09-05 23:47:29');
INSERT INTO public.role_user VALUES (72, 11, 72, '2025-09-05 23:47:30', NULL, NULL, true, '2025-09-05 23:47:30', '2025-09-05 23:47:30');
INSERT INTO public.role_user VALUES (73, 11, 73, '2025-09-05 23:47:30', NULL, NULL, true, '2025-09-05 23:47:30', '2025-09-05 23:47:30');
INSERT INTO public.role_user VALUES (74, 11, 74, '2025-09-05 23:47:30', NULL, NULL, true, '2025-09-05 23:47:30', '2025-09-05 23:47:30');
INSERT INTO public.role_user VALUES (75, 11, 75, '2025-09-05 23:47:30', NULL, NULL, true, '2025-09-05 23:47:30', '2025-09-05 23:47:30');
INSERT INTO public.role_user VALUES (76, 11, 76, '2025-09-05 23:47:31', NULL, NULL, true, '2025-09-05 23:47:31', '2025-09-05 23:47:31');
INSERT INTO public.role_user VALUES (77, 11, 77, '2025-09-05 23:47:31', NULL, NULL, true, '2025-09-05 23:47:31', '2025-09-05 23:47:31');
INSERT INTO public.role_user VALUES (78, 11, 78, '2025-09-05 23:47:31', NULL, NULL, true, '2025-09-05 23:47:31', '2025-09-05 23:47:31');
INSERT INTO public.role_user VALUES (79, 11, 79, '2025-09-05 23:47:31', NULL, NULL, true, '2025-09-05 23:47:31', '2025-09-05 23:47:31');
INSERT INTO public.role_user VALUES (80, 11, 80, '2025-09-05 23:47:32', NULL, NULL, true, '2025-09-05 23:47:32', '2025-09-05 23:47:32');
INSERT INTO public.role_user VALUES (81, 11, 81, '2025-09-05 23:47:32', NULL, NULL, true, '2025-09-05 23:47:32', '2025-09-05 23:47:32');
INSERT INTO public.role_user VALUES (82, 11, 82, '2025-09-05 23:47:32', NULL, NULL, true, '2025-09-05 23:47:32', '2025-09-05 23:47:32');
INSERT INTO public.role_user VALUES (83, 11, 83, '2025-09-05 23:47:32', NULL, NULL, true, '2025-09-05 23:47:32', '2025-09-05 23:47:32');
INSERT INTO public.role_user VALUES (84, 11, 84, '2025-09-05 23:47:33', NULL, NULL, true, '2025-09-05 23:47:33', '2025-09-05 23:47:33');
INSERT INTO public.role_user VALUES (85, 11, 85, '2025-09-05 23:47:33', NULL, NULL, true, '2025-09-05 23:47:33', '2025-09-05 23:47:33');
INSERT INTO public.role_user VALUES (86, 11, 86, '2025-09-05 23:47:33', NULL, NULL, true, '2025-09-05 23:47:33', '2025-09-05 23:47:33');
INSERT INTO public.role_user VALUES (87, 11, 87, '2025-09-05 23:47:33', NULL, NULL, true, '2025-09-05 23:47:33', '2025-09-05 23:47:33');
INSERT INTO public.role_user VALUES (88, 11, 88, '2025-09-05 23:47:34', NULL, NULL, true, '2025-09-05 23:47:34', '2025-09-05 23:47:34');
INSERT INTO public.role_user VALUES (89, 11, 89, '2025-09-05 23:47:34', NULL, NULL, true, '2025-09-05 23:47:34', '2025-09-05 23:47:34');
INSERT INTO public.role_user VALUES (90, 11, 90, '2025-09-05 23:47:34', NULL, NULL, true, '2025-09-05 23:47:34', '2025-09-05 23:47:34');
INSERT INTO public.role_user VALUES (91, 11, 91, '2025-09-05 23:47:35', NULL, NULL, true, '2025-09-05 23:47:35', '2025-09-05 23:47:35');
INSERT INTO public.role_user VALUES (92, 11, 92, '2025-09-05 23:47:35', NULL, NULL, true, '2025-09-05 23:47:35', '2025-09-05 23:47:35');
INSERT INTO public.role_user VALUES (93, 11, 93, '2025-09-05 23:47:35', NULL, NULL, true, '2025-09-05 23:47:35', '2025-09-05 23:47:35');
INSERT INTO public.role_user VALUES (94, 11, 94, '2025-09-05 23:47:35', NULL, NULL, true, '2025-09-05 23:47:35', '2025-09-05 23:47:35');
INSERT INTO public.role_user VALUES (95, 11, 95, '2025-09-05 23:47:36', NULL, NULL, true, '2025-09-05 23:47:36', '2025-09-05 23:47:36');
INSERT INTO public.role_user VALUES (96, 11, 96, '2025-09-05 23:47:36', NULL, NULL, true, '2025-09-05 23:47:36', '2025-09-05 23:47:36');
INSERT INTO public.role_user VALUES (97, 11, 97, '2025-09-05 23:47:36', NULL, NULL, true, '2025-09-05 23:47:36', '2025-09-05 23:47:36');
INSERT INTO public.role_user VALUES (98, 11, 98, '2025-09-05 23:47:37', NULL, NULL, true, '2025-09-05 23:47:37', '2025-09-05 23:47:37');
INSERT INTO public.role_user VALUES (99, 11, 99, '2025-09-05 23:47:37', NULL, NULL, true, '2025-09-05 23:47:37', '2025-09-05 23:47:37');
INSERT INTO public.role_user VALUES (100, 11, 100, '2025-09-05 23:47:37', NULL, NULL, true, '2025-09-05 23:47:37', '2025-09-05 23:47:37');
INSERT INTO public.role_user VALUES (101, 11, 101, '2025-09-05 23:47:37', NULL, NULL, true, '2025-09-05 23:47:37', '2025-09-05 23:47:37');
INSERT INTO public.role_user VALUES (102, 11, 102, '2025-09-05 23:47:38', NULL, NULL, true, '2025-09-05 23:47:38', '2025-09-05 23:47:38');
INSERT INTO public.role_user VALUES (103, 11, 103, '2025-09-05 23:47:38', NULL, NULL, true, '2025-09-05 23:47:38', '2025-09-05 23:47:38');
INSERT INTO public.role_user VALUES (104, 11, 104, '2025-09-05 23:47:38', NULL, NULL, true, '2025-09-05 23:47:38', '2025-09-05 23:47:38');
INSERT INTO public.role_user VALUES (105, 11, 105, '2025-09-05 23:47:38', NULL, NULL, true, '2025-09-05 23:47:38', '2025-09-05 23:47:38');
INSERT INTO public.role_user VALUES (106, 11, 106, '2025-09-05 23:47:39', NULL, NULL, true, '2025-09-05 23:47:39', '2025-09-05 23:47:39');
INSERT INTO public.role_user VALUES (107, 11, 107, '2025-09-05 23:47:39', NULL, NULL, true, '2025-09-05 23:47:39', '2025-09-05 23:47:39');
INSERT INTO public.role_user VALUES (108, 11, 108, '2025-09-05 23:47:39', NULL, NULL, true, '2025-09-05 23:47:39', '2025-09-05 23:47:39');
INSERT INTO public.role_user VALUES (109, 11, 109, '2025-09-05 23:47:39', NULL, NULL, true, '2025-09-05 23:47:39', '2025-09-05 23:47:39');
INSERT INTO public.role_user VALUES (110, 11, 110, '2025-09-05 23:47:40', NULL, NULL, true, '2025-09-05 23:47:40', '2025-09-05 23:47:40');
INSERT INTO public.role_user VALUES (111, 11, 111, '2025-09-05 23:47:40', NULL, NULL, true, '2025-09-05 23:47:40', '2025-09-05 23:47:40');
INSERT INTO public.role_user VALUES (112, 11, 112, '2025-09-05 23:47:40', NULL, NULL, true, '2025-09-05 23:47:40', '2025-09-05 23:47:40');
INSERT INTO public.role_user VALUES (113, 11, 113, '2025-09-05 23:47:40', NULL, NULL, true, '2025-09-05 23:47:40', '2025-09-05 23:47:40');
INSERT INTO public.role_user VALUES (114, 11, 114, '2025-09-05 23:47:41', NULL, NULL, true, '2025-09-05 23:47:41', '2025-09-05 23:47:41');
INSERT INTO public.role_user VALUES (115, 11, 115, '2025-09-05 23:47:41', NULL, NULL, true, '2025-09-05 23:47:41', '2025-09-05 23:47:41');
INSERT INTO public.role_user VALUES (116, 11, 116, '2025-09-05 23:47:41', NULL, NULL, true, '2025-09-05 23:47:41', '2025-09-05 23:47:41');
INSERT INTO public.role_user VALUES (117, 11, 117, '2025-09-05 23:47:41', NULL, NULL, true, '2025-09-05 23:47:41', '2025-09-05 23:47:41');
INSERT INTO public.role_user VALUES (118, 11, 118, '2025-09-05 23:47:42', NULL, NULL, true, '2025-09-05 23:47:42', '2025-09-05 23:47:42');
INSERT INTO public.role_user VALUES (119, 11, 119, '2025-09-05 23:47:42', NULL, NULL, true, '2025-09-05 23:47:42', '2025-09-05 23:47:42');
INSERT INTO public.role_user VALUES (120, 11, 120, '2025-09-05 23:47:42', NULL, NULL, true, '2025-09-05 23:47:42', '2025-09-05 23:47:42');
INSERT INTO public.role_user VALUES (121, 11, 121, '2025-09-05 23:47:43', NULL, NULL, true, '2025-09-05 23:47:43', '2025-09-05 23:47:43');
INSERT INTO public.role_user VALUES (122, 11, 122, '2025-09-05 23:47:43', NULL, NULL, true, '2025-09-05 23:47:43', '2025-09-05 23:47:43');
INSERT INTO public.role_user VALUES (123, 11, 123, '2025-09-05 23:47:43', NULL, NULL, true, '2025-09-05 23:47:43', '2025-09-05 23:47:43');
INSERT INTO public.role_user VALUES (124, 11, 124, '2025-09-05 23:47:44', NULL, NULL, true, '2025-09-05 23:47:44', '2025-09-05 23:47:44');
INSERT INTO public.role_user VALUES (125, 11, 125, '2025-09-05 23:47:44', NULL, NULL, true, '2025-09-05 23:47:44', '2025-09-05 23:47:44');
INSERT INTO public.role_user VALUES (126, 11, 126, '2025-09-05 23:47:44', NULL, NULL, true, '2025-09-05 23:47:44', '2025-09-05 23:47:44');
INSERT INTO public.role_user VALUES (127, 11, 127, '2025-09-05 23:47:44', NULL, NULL, true, '2025-09-05 23:47:44', '2025-09-05 23:47:44');
INSERT INTO public.role_user VALUES (128, 11, 128, '2025-09-05 23:47:45', NULL, NULL, true, '2025-09-05 23:47:45', '2025-09-05 23:47:45');
INSERT INTO public.role_user VALUES (129, 11, 129, '2025-09-05 23:47:45', NULL, NULL, true, '2025-09-05 23:47:45', '2025-09-05 23:47:45');
INSERT INTO public.role_user VALUES (130, 11, 130, '2025-09-05 23:47:45', NULL, NULL, true, '2025-09-05 23:47:45', '2025-09-05 23:47:45');
INSERT INTO public.role_user VALUES (131, 11, 131, '2025-09-05 23:47:45', NULL, NULL, true, '2025-09-05 23:47:45', '2025-09-05 23:47:45');
INSERT INTO public.role_user VALUES (132, 11, 132, '2025-09-05 23:47:46', NULL, NULL, true, '2025-09-05 23:47:46', '2025-09-05 23:47:46');
INSERT INTO public.role_user VALUES (133, 11, 133, '2025-09-05 23:47:46', NULL, NULL, true, '2025-09-05 23:47:46', '2025-09-05 23:47:46');
INSERT INTO public.role_user VALUES (134, 11, 134, '2025-09-05 23:47:46', NULL, NULL, true, '2025-09-05 23:47:46', '2025-09-05 23:47:46');
INSERT INTO public.role_user VALUES (135, 11, 135, '2025-09-05 23:47:46', NULL, NULL, true, '2025-09-05 23:47:46', '2025-09-05 23:47:46');
INSERT INTO public.role_user VALUES (136, 11, 136, '2025-09-05 23:47:47', NULL, NULL, true, '2025-09-05 23:47:47', '2025-09-05 23:47:47');
INSERT INTO public.role_user VALUES (137, 11, 137, '2025-09-05 23:47:47', NULL, NULL, true, '2025-09-05 23:47:47', '2025-09-05 23:47:47');
INSERT INTO public.role_user VALUES (138, 11, 138, '2025-09-05 23:47:47', NULL, NULL, true, '2025-09-05 23:47:47', '2025-09-05 23:47:47');
INSERT INTO public.role_user VALUES (139, 11, 139, '2025-09-05 23:47:48', NULL, NULL, true, '2025-09-05 23:47:48', '2025-09-05 23:47:48');
INSERT INTO public.role_user VALUES (140, 11, 140, '2025-09-05 23:47:48', NULL, NULL, true, '2025-09-05 23:47:48', '2025-09-05 23:47:48');
INSERT INTO public.role_user VALUES (141, 11, 141, '2025-09-05 23:47:48', NULL, NULL, true, '2025-09-05 23:47:48', '2025-09-05 23:47:48');
INSERT INTO public.role_user VALUES (142, 11, 142, '2025-09-05 23:47:48', NULL, NULL, true, '2025-09-05 23:47:48', '2025-09-05 23:47:48');
INSERT INTO public.role_user VALUES (143, 11, 143, '2025-09-05 23:47:49', NULL, NULL, true, '2025-09-05 23:47:49', '2025-09-05 23:47:49');
INSERT INTO public.role_user VALUES (144, 11, 144, '2025-09-05 23:47:49', NULL, NULL, true, '2025-09-05 23:47:49', '2025-09-05 23:47:49');
INSERT INTO public.role_user VALUES (145, 11, 145, '2025-09-05 23:47:49', NULL, NULL, true, '2025-09-05 23:47:49', '2025-09-05 23:47:49');
INSERT INTO public.role_user VALUES (146, 11, 146, '2025-09-05 23:47:49', NULL, NULL, true, '2025-09-05 23:47:49', '2025-09-05 23:47:49');
INSERT INTO public.role_user VALUES (147, 11, 147, '2025-09-05 23:47:50', NULL, NULL, true, '2025-09-05 23:47:50', '2025-09-05 23:47:50');
INSERT INTO public.role_user VALUES (148, 11, 148, '2025-09-05 23:47:50', NULL, NULL, true, '2025-09-05 23:47:50', '2025-09-05 23:47:50');
INSERT INTO public.role_user VALUES (149, 11, 149, '2025-09-05 23:47:50', NULL, NULL, true, '2025-09-05 23:47:50', '2025-09-05 23:47:50');
INSERT INTO public.role_user VALUES (150, 11, 150, '2025-09-05 23:47:50', NULL, NULL, true, '2025-09-05 23:47:50', '2025-09-05 23:47:50');
INSERT INTO public.role_user VALUES (151, 11, 151, '2025-09-05 23:47:51', NULL, NULL, true, '2025-09-05 23:47:51', '2025-09-05 23:47:51');
INSERT INTO public.role_user VALUES (152, 11, 152, '2025-09-05 23:47:51', NULL, NULL, true, '2025-09-05 23:47:51', '2025-09-05 23:47:51');
INSERT INTO public.role_user VALUES (153, 11, 153, '2025-09-05 23:47:51', NULL, NULL, true, '2025-09-05 23:47:51', '2025-09-05 23:47:51');
INSERT INTO public.role_user VALUES (154, 11, 154, '2025-09-05 23:47:51', NULL, NULL, true, '2025-09-05 23:47:51', '2025-09-05 23:47:51');
INSERT INTO public.role_user VALUES (155, 11, 155, '2025-09-06 00:39:34', NULL, NULL, true, '2025-09-06 00:39:34', '2025-09-06 00:39:34');
INSERT INTO public.role_user VALUES (156, 2, 48, NULL, NULL, NULL, false, '2025-09-07 10:41:06', '2025-09-07 10:41:06');
INSERT INTO public.role_user VALUES (160, 17, 160, '2025-09-12 15:49:15', 1, NULL, true, '2025-09-12 15:49:15', '2025-09-12 15:49:15');
INSERT INTO public.role_user VALUES (161, 17, 161, '2025-09-12 15:49:16', 1, NULL, true, '2025-09-12 15:49:16', '2025-09-12 15:49:16');
INSERT INTO public.role_user VALUES (162, 18, 162, '2025-09-12 15:49:16', 1, NULL, true, '2025-09-12 15:49:16', '2025-09-12 15:49:16');
INSERT INTO public.role_user VALUES (164, 19, 175, '2025-09-17 15:38:42', NULL, NULL, true, '2025-09-17 15:38:42', '2025-09-17 15:38:42');
INSERT INTO public.role_user VALUES (165, 19, 176, '2025-09-17 15:38:43', NULL, NULL, true, '2025-09-17 15:38:43', '2025-09-17 15:38:43');
INSERT INTO public.role_user VALUES (166, 19, 177, '2025-09-17 15:38:43', NULL, NULL, true, '2025-09-17 15:38:43', '2025-09-17 15:38:43');
INSERT INTO public.role_user VALUES (167, 19, 178, '2025-09-17 15:38:43', NULL, NULL, true, '2025-09-17 15:38:43', '2025-09-17 15:38:43');
INSERT INTO public.role_user VALUES (168, 19, 179, '2025-09-17 15:38:44', NULL, NULL, true, '2025-09-17 15:38:44', '2025-09-17 15:38:44');
INSERT INTO public.role_user VALUES (169, 19, 180, '2025-09-17 15:38:44', NULL, NULL, true, '2025-09-17 15:38:44', '2025-09-17 15:38:44');
INSERT INTO public.role_user VALUES (170, 19, 181, '2025-09-17 15:38:44', NULL, NULL, true, '2025-09-17 15:38:44', '2025-09-17 15:38:44');
INSERT INTO public.role_user VALUES (171, 19, 182, '2025-09-17 15:38:45', NULL, NULL, true, '2025-09-17 15:38:45', '2025-09-17 15:38:45');
INSERT INTO public.role_user VALUES (172, 19, 183, '2025-09-17 15:38:45', NULL, NULL, true, '2025-09-17 15:38:45', '2025-09-17 15:38:45');
INSERT INTO public.role_user VALUES (173, 19, 184, '2025-09-17 15:38:45', NULL, NULL, true, '2025-09-17 15:38:45', '2025-09-17 15:38:45');
INSERT INTO public.role_user VALUES (174, 19, 185, '2025-09-17 15:38:45', NULL, NULL, true, '2025-09-17 15:38:45', '2025-09-17 15:38:45');
INSERT INTO public.role_user VALUES (175, 19, 186, '2025-09-17 15:38:46', NULL, NULL, true, '2025-09-17 15:38:46', '2025-09-17 15:38:46');
INSERT INTO public.role_user VALUES (176, 19, 187, '2025-09-17 15:38:46', NULL, NULL, true, '2025-09-17 15:38:46', '2025-09-17 15:38:46');
INSERT INTO public.role_user VALUES (177, 19, 188, '2025-09-17 15:38:46', NULL, NULL, true, '2025-09-17 15:38:46', '2025-09-17 15:38:46');
INSERT INTO public.role_user VALUES (178, 19, 189, '2025-09-17 15:38:46', NULL, NULL, true, '2025-09-17 15:38:46', '2025-09-17 15:38:46');
INSERT INTO public.role_user VALUES (179, 19, 190, '2025-09-17 15:38:46', NULL, NULL, true, '2025-09-17 15:38:46', '2025-09-17 15:38:46');
INSERT INTO public.role_user VALUES (180, 19, 191, '2025-09-17 15:38:47', NULL, NULL, true, '2025-09-17 15:38:47', '2025-09-17 15:38:47');
INSERT INTO public.role_user VALUES (181, 19, 192, '2025-09-17 15:38:47', NULL, NULL, true, '2025-09-17 15:38:47', '2025-09-17 15:38:47');
INSERT INTO public.role_user VALUES (182, 19, 193, '2025-09-17 15:38:47', NULL, NULL, true, '2025-09-17 15:38:47', '2025-09-17 15:38:47');
INSERT INTO public.role_user VALUES (183, 19, 194, '2025-09-17 15:38:47', NULL, NULL, true, '2025-09-17 15:38:47', '2025-09-17 15:38:47');
INSERT INTO public.role_user VALUES (184, 19, 195, '2025-09-17 15:38:48', NULL, NULL, true, '2025-09-17 15:38:48', '2025-09-17 15:38:48');
INSERT INTO public.role_user VALUES (185, 19, 196, '2025-09-17 15:38:48', NULL, NULL, true, '2025-09-17 15:38:48', '2025-09-17 15:38:48');


--
-- Data for Name: room_availability; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: room_bookings; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: rubrics; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: rubric_criteria; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: schedule_changes; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: schedule_conflicts; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: scope_audit_logs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: section_attendance_policies; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: section_schedules; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: special_registration_flags; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: third_party_sponsors; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: sponsor_authorizations; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: student_conversions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: student_course_applications; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: student_degree_progress; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.student_degree_progress VALUES (1, 11, 17, 17, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:03', '2025-09-09 15:25:59', '2025-09-09 15:56:03');
INSERT INTO public.student_degree_progress VALUES (2, 11, 18, 18, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:03', '2025-09-09 15:25:59', '2025-09-09 15:56:03');
INSERT INTO public.student_degree_progress VALUES (3, 11, 19, 19, 31.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:03', '2025-09-09 15:25:59', '2025-09-09 15:56:03');
INSERT INTO public.student_degree_progress VALUES (4, 11, 20, 20, 31.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:03', '2025-09-09 15:25:59', '2025-09-09 15:56:03');
INSERT INTO public.student_degree_progress VALUES (5, 11, 21, 21, 31.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:03', '2025-09-09 15:25:59', '2025-09-09 15:56:03');
INSERT INTO public.student_degree_progress VALUES (6, 11, 22, 22, 0.0, 0.0, 0.0, 0, 0, 0, 'completed', 0.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (7, 11, 23, 23, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (8, 11, 24, 24, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (9, 11, 25, 25, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (10, 11, 26, 26, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (11, 11, 27, 27, 31.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (12, 11, 28, 28, 31.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (13, 11, 29, 29, 31.0, 0.0, 89.0, 0, 0, 0, 'in_progress', 25.83, false, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (14, 11, 30, 30, 0.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (15, 11, 31, 31, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (16, 11, 32, 32, 0.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-09 15:56:04', '2025-09-09 15:25:59', '2025-09-09 15:56:04');
INSERT INTO public.student_degree_progress VALUES (22, 94, 21, 21, 59.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (23, 94, 22, 22, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (24, 94, 23, 23, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (25, 94, 24, 24, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (26, 94, 25, 25, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (27, 94, 26, 26, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (28, 94, 27, 27, 59.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (29, 94, 28, 28, 59.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (30, 94, 29, 29, 59.0, 0.0, 61.0, 0, 0, 0, 'in_progress', 49.17, false, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (31, 94, 30, 30, 0.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (32, 94, 31, 31, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (33, 94, 32, 32, 0.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (17, 94, 17, 17, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (19, 94, 18, 18, 0.0, 0.0, 0.0, 0, 0, 0, 'not_started', 0.00, false, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (20, 94, 19, 19, 59.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');
INSERT INTO public.student_degree_progress VALUES (21, 94, 20, 20, 59.0, 0.0, 0.0, 0, 0, 0, 'completed', 100.00, true, NULL, false, NULL, false, NULL, NULL, '2025-09-10 13:55:11', '2025-09-10 12:36:22', '2025-09-10 13:55:11');


--
-- Data for Name: student_holds; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: student_honors; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: student_progress; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: student_status_changes; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: system_modules; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.system_modules VALUES (1, 'Student Management', 'STUDENT_MGMT', NULL, true, NULL, NULL, 1, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_modules VALUES (2, 'Course Management', 'COURSE_MGMT', NULL, true, NULL, NULL, 2, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_modules VALUES (3, 'Registration', 'REGISTRATION', NULL, true, NULL, NULL, 3, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_modules VALUES (4, 'Grading', 'GRADING', NULL, true, NULL, NULL, 4, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_modules VALUES (5, 'Financial', 'FINANCIAL', NULL, true, NULL, NULL, 5, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_modules VALUES (6, 'LMS', 'LMS', NULL, true, NULL, NULL, 6, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_modules VALUES (7, 'Scheduling', 'SCHEDULING', NULL, true, NULL, NULL, 7, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_modules VALUES (8, 'Examination', 'EXAMINATION', NULL, true, NULL, NULL, 8, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_modules VALUES (9, 'Attendance', 'ATTENDANCE', NULL, true, NULL, NULL, 9, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_modules VALUES (10, 'Admissions', 'ADMISSIONS', NULL, true, NULL, NULL, 10, '2025-09-07 09:58:49', '2025-09-07 09:58:49');


--
-- Data for Name: system_settings; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.system_settings VALUES (1, 'general', 'maintenance_mode', 'false', 'boolean', 'Enable maintenance mode', NULL, false, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_settings VALUES (2, 'general', 'allow_registration', 'true', 'boolean', 'Allow new user registration', NULL, false, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_settings VALUES (3, 'general', 'default_language', 'en', 'text', 'Default system language', NULL, false, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_settings VALUES (4, 'academic', 'current_term_id', '1', 'number', 'Current academic term', NULL, false, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_settings VALUES (5, 'academic', 'allow_course_shopping', 'true', 'boolean', 'Allow course shopping period', NULL, false, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_settings VALUES (6, 'academic', 'shopping_period_days', '7', 'number', 'Course shopping period in days', NULL, false, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_settings VALUES (7, 'financial', 'payment_gateway', 'stripe', 'text', 'Active payment gateway', NULL, false, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_settings VALUES (8, 'financial', 'allow_partial_payment', 'true', 'boolean', 'Allow partial payments', NULL, false, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_settings VALUES (9, 'financial', 'late_payment_fee', '25', 'number', 'Late payment fee amount', NULL, false, true, '2025-09-07 09:58:49', '2025-09-07 09:58:49');
INSERT INTO public.system_settings VALUES (11, 'admissions', 'application_fee', '50.00', 'decimal', 'Application processing fee', NULL, false, true, '2025-09-21 21:13:26', '2025-09-21 21:13:26');
INSERT INTO public.system_settings VALUES (12, 'admissions', 'processing_fee', '0.00', 'decimal', 'Additional processing fee', NULL, false, true, '2025-09-21 21:14:01', '2025-09-21 21:14:01');
INSERT INTO public.system_settings VALUES (13, 'payments', 'enable_mobile_money', 'false', 'boolean', 'Enable mobile money payments', NULL, false, true, '2025-09-21 21:14:22', '2025-09-21 21:14:22');


--
-- Data for Name: teaching_assistants; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: teaching_loads; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: time_slots; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.time_slots VALUES (1, 'Period 1', '08:00:00', '09:00:00', 60, 'regular', true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.time_slots VALUES (2, 'Period 2', '09:00:00', '10:00:00', 60, 'regular', true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.time_slots VALUES (3, 'Period 3', '10:00:00', '11:00:00', 60, 'regular', true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.time_slots VALUES (4, 'Period 4', '11:00:00', '12:00:00', 60, 'regular', true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.time_slots VALUES (5, 'Lunch Break', '12:00:00', '13:00:00', 60, 'break', true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.time_slots VALUES (6, 'Period 5', '13:00:00', '14:00:00', 60, 'regular', true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.time_slots VALUES (7, 'Period 6', '14:00:00', '15:00:00', 60, 'regular', true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.time_slots VALUES (8, 'Period 7', '15:00:00', '16:00:00', 60, 'regular', true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');
INSERT INTO public.time_slots VALUES (9, 'Period 8', '16:00:00', '17:00:00', 60, 'regular', true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');


--
-- Data for Name: timetable_templates; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.timetable_templates VALUES (1, 'Standard Weekly Schedule', 'standard', '[{"slot_name":"Period 1","start_time":"08:00","end_time":"09:00","duration_minutes":60},{"slot_name":"Period 2","start_time":"09:00","end_time":"10:00","duration_minutes":60},{"slot_name":"Period 3","start_time":"10:00","end_time":"11:00","duration_minutes":60},{"slot_name":"Period 4","start_time":"11:00","end_time":"12:00","duration_minutes":60},{"slot_name":"Lunch Break","start_time":"12:00","end_time":"13:00","duration_minutes":60,"slot_type":"break"},{"slot_name":"Period 5","start_time":"13:00","end_time":"14:00","duration_minutes":60},{"slot_name":"Period 6","start_time":"14:00","end_time":"15:00","duration_minutes":60},{"slot_name":"Period 7","start_time":"15:00","end_time":"16:00","duration_minutes":60},{"slot_name":"Period 8","start_time":"16:00","end_time":"17:00","duration_minutes":60}]', '[{"start":"10:00","end":"10:15","name":"Morning Break"},{"start":"12:00","end":"13:00","name":"Lunch Break"},{"start":"15:00","end":"15:15","name":"Afternoon Break"}]', 5, '08:00:00', '17:00:00', false, false, true, true, '2025-09-07 10:00:03', '2025-09-07 10:00:03');


--
-- Data for Name: transcript_requests; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.transcript_requests VALUES (4, 'TR2025090001', 11, 1, 'official', 'electronic', 1, 'Test University', 'test@university.edu', NULL, 'Graduate School Application', false, NULL, 10.00, 'pending', NULL, NULL, 'pending', '2025-09-06 21:31:37', NULL, NULL, NULL, NULL, '32D1-3751-036E', NULL, NULL, '2025-09-06 21:31:37', '2025-09-06 21:31:37', NULL);
INSERT INTO public.transcript_requests VALUES (5, 'TR2025000002', 13, 48, 'official', 'electronic', 1, 'Diggy Solutions', 'diggygibson.rg@gmail.com', NULL, 'Employment', false, NULL, 10.00, 'pending', NULL, NULL, 'pending', '2025-09-07 05:48:50', NULL, NULL, NULL, NULL, '13AE-6600-57D8', NULL, NULL, '2025-09-07 05:48:50', '2025-09-07 05:48:50', NULL);


--
-- Data for Name: transcript_logs; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.transcript_logs VALUES (1, 105, NULL, 'viewed', 'unofficial', NULL, 155, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', NULL, '2025-09-07 05:10:11', '2025-09-07 05:10:11', '2025-09-07 05:10:11');
INSERT INTO public.transcript_logs VALUES (2, 105, NULL, 'generated', 'unofficial', NULL, 155, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', NULL, '2025-09-07 05:10:25', '2025-09-07 05:10:25', '2025-09-07 05:10:25');
INSERT INTO public.transcript_logs VALUES (3, 105, NULL, 'downloaded', 'unofficial', NULL, 155, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', NULL, '2025-09-07 05:10:28', '2025-09-07 05:10:28', '2025-09-07 05:10:28');
INSERT INTO public.transcript_logs VALUES (4, 25, NULL, 'viewed', 'unofficial', NULL, 48, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', NULL, '2025-09-07 05:17:22', '2025-09-07 05:17:22', '2025-09-07 05:17:22');
INSERT INTO public.transcript_logs VALUES (5, 13, 5, 'requested', 'official', 'Employment', 48, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', NULL, '2025-09-07 05:48:50', '2025-09-07 05:48:50', '2025-09-07 05:48:50');
INSERT INTO public.transcript_logs VALUES (6, 44, NULL, 'viewed', 'unofficial', NULL, 48, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', NULL, '2025-09-07 18:55:14', '2025-09-07 18:55:14', '2025-09-07 18:55:14');


--
-- Data for Name: transcript_payments; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: transcript_requests_backup_20250915_133256; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.transcript_requests_backup_20250915_133256 VALUES (4, 'TR2025090001', 11, 1, 'official', 'electronic', 1, 'Test University', 'test@university.edu', NULL, 'Graduate School Application', false, NULL, 10.00, 'pending', NULL, NULL, 'pending', '2025-09-06 21:31:37', NULL, NULL, NULL, NULL, '32D1-3751-036E', NULL, NULL, '2025-09-06 21:31:37', '2025-09-06 21:31:37', NULL);
INSERT INTO public.transcript_requests_backup_20250915_133256 VALUES (5, 'TR2025000002', 13, 48, 'official', 'electronic', 1, 'Diggy Solutions', 'diggygibson.rg@gmail.com', NULL, 'Employment', false, NULL, 10.00, 'pending', NULL, NULL, 'pending', '2025-09-07 05:48:50', NULL, NULL, NULL, NULL, '13AE-6600-57D8', NULL, NULL, '2025-09-07 05:48:50', '2025-09-07 05:48:50', NULL);


--
-- Data for Name: transcript_verifications; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: transfer_credits; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: user_activity_logs; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.user_activity_logs VALUES (1, NULL, 'role_change', 'Assigned roles for user Dean CAS', 'App\Models\User', 1, '{"roles": ["Dean"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:49', '2025-09-04 22:28:49', '2025-09-04 22:28:49');
INSERT INTO public.user_activity_logs VALUES (2, NULL, 'role_change', 'Assigned roles for user Associate Dean CAS', 'App\Models\User', 2, '{"roles": ["Dean"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:49', '2025-09-04 22:28:49', '2025-09-04 22:28:49');
INSERT INTO public.user_activity_logs VALUES (3, NULL, 'role_change', 'Assigned roles for user Dean COE', 'App\Models\User', 3, '{"roles": ["Dean"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:49', '2025-09-04 22:28:49', '2025-09-04 22:28:49');
INSERT INTO public.user_activity_logs VALUES (4, NULL, 'role_change', 'Assigned roles for user Associate Dean COE', 'App\Models\User', 4, '{"roles": ["Dean"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:49', '2025-09-04 22:28:49', '2025-09-04 22:28:49');
INSERT INTO public.user_activity_logs VALUES (5, NULL, 'role_change', 'Assigned roles for user Dean COB', 'App\Models\User', 5, '{"roles": ["Dean"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:49', '2025-09-04 22:28:49', '2025-09-04 22:28:49');
INSERT INTO public.user_activity_logs VALUES (6, NULL, 'role_change', 'Assigned roles for user Associate Dean COB', 'App\Models\User', 6, '{"roles": ["Dean"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:50', '2025-09-04 22:28:50', '2025-09-04 22:28:50');
INSERT INTO public.user_activity_logs VALUES (7, NULL, 'role_change', 'Assigned roles for user Dean COM', 'App\Models\User', 7, '{"roles": ["Dean"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:50', '2025-09-04 22:28:50', '2025-09-04 22:28:50');
INSERT INTO public.user_activity_logs VALUES (8, NULL, 'role_change', 'Assigned roles for user Associate Dean COM', 'App\Models\User', 8, '{"roles": ["Dean"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:50', '2025-09-04 22:28:50', '2025-09-04 22:28:50');
INSERT INTO public.user_activity_logs VALUES (9, NULL, 'role_change', 'Assigned roles for user Director SCS', 'App\Models\User', 9, '{"roles": ["Department Head"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:50', '2025-09-04 22:28:50', '2025-09-04 22:28:50');
INSERT INTO public.user_activity_logs VALUES (10, NULL, 'role_change', 'Assigned roles for user Director SNS', 'App\Models\User', 10, '{"roles": ["Department Head"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:51', '2025-09-04 22:28:51', '2025-09-04 22:28:51');
INSERT INTO public.user_activity_logs VALUES (11, NULL, 'role_change', 'Assigned roles for user Head MATH', 'App\Models\User', 11, '{"roles": ["Department Head"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:51', '2025-09-04 22:28:51', '2025-09-04 22:28:51');
INSERT INTO public.user_activity_logs VALUES (12, NULL, 'role_change', 'Assigned roles for user Head PHYS', 'App\Models\User', 12, '{"roles": ["Department Head"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:51', '2025-09-04 22:28:51', '2025-09-04 22:28:51');
INSERT INTO public.user_activity_logs VALUES (13, NULL, 'role_change', 'Assigned roles for user Head ENGL', 'App\Models\User', 13, '{"roles": ["Department Head"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:51', '2025-09-04 22:28:51', '2025-09-04 22:28:51');
INSERT INTO public.user_activity_logs VALUES (14, NULL, 'role_change', 'Assigned roles for user Head ECE', 'App\Models\User', 14, '{"roles": ["Department Head"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:51', '2025-09-04 22:28:51', '2025-09-04 22:28:51');
INSERT INTO public.user_activity_logs VALUES (15, NULL, 'role_change', 'Assigned roles for user Head MECH', 'App\Models\User', 15, '{"roles": ["Department Head"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.user_activity_logs VALUES (16, NULL, 'role_change', 'Assigned roles for user Head CS', 'App\Models\User', 16, '{"roles": ["Department Head"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.user_activity_logs VALUES (17, NULL, 'role_change', 'Assigned roles for user Head ACCT', 'App\Models\User', 17, '{"roles": ["Department Head"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.user_activity_logs VALUES (18, NULL, 'role_change', 'Assigned roles for user Head MGMT', 'App\Models\User', 18, '{"roles": ["Department Head"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.user_activity_logs VALUES (19, NULL, 'role_change', 'Assigned roles for user Prof. MATH 1', 'App\Models\User', 19, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.user_activity_logs VALUES (20, NULL, 'role_change', 'Assigned roles for user Prof. MATH 2', 'App\Models\User', 20, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.user_activity_logs VALUES (21, NULL, 'role_change', 'Assigned roles for user Prof. MATH 3', 'App\Models\User', 21, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.user_activity_logs VALUES (22, NULL, 'role_change', 'Assigned roles for user Prof. PHYS 1', 'App\Models\User', 22, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.user_activity_logs VALUES (23, NULL, 'role_change', 'Assigned roles for user Prof. PHYS 2', 'App\Models\User', 23, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.user_activity_logs VALUES (24, NULL, 'role_change', 'Assigned roles for user Prof. PHYS 3', 'App\Models\User', 24, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.user_activity_logs VALUES (25, NULL, 'role_change', 'Assigned roles for user Prof. PHYS 4', 'App\Models\User', 25, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.user_activity_logs VALUES (26, NULL, 'role_change', 'Assigned roles for user Prof. ENGL 1', 'App\Models\User', 26, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.user_activity_logs VALUES (27, NULL, 'role_change', 'Assigned roles for user Prof. ENGL 2', 'App\Models\User', 27, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.user_activity_logs VALUES (28, NULL, 'role_change', 'Assigned roles for user Prof. ENGL 3', 'App\Models\User', 28, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.user_activity_logs VALUES (29, NULL, 'role_change', 'Assigned roles for user Prof. ENGL 4', 'App\Models\User', 29, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.user_activity_logs VALUES (30, NULL, 'role_change', 'Assigned roles for user Prof. ENGL 5', 'App\Models\User', 30, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.user_activity_logs VALUES (31, NULL, 'role_change', 'Assigned roles for user Prof. ECE 1', 'App\Models\User', 31, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.user_activity_logs VALUES (32, NULL, 'role_change', 'Assigned roles for user Prof. ECE 2', 'App\Models\User', 32, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.user_activity_logs VALUES (33, NULL, 'role_change', 'Assigned roles for user Prof. ECE 3', 'App\Models\User', 33, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.user_activity_logs VALUES (34, NULL, 'role_change', 'Assigned roles for user Prof. MECH 1', 'App\Models\User', 34, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.user_activity_logs VALUES (35, NULL, 'role_change', 'Assigned roles for user Prof. MECH 2', 'App\Models\User', 35, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.user_activity_logs VALUES (36, NULL, 'role_change', 'Assigned roles for user Prof. MECH 3', 'App\Models\User', 36, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.user_activity_logs VALUES (37, NULL, 'role_change', 'Assigned roles for user Prof. MECH 4', 'App\Models\User', 37, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.user_activity_logs VALUES (38, NULL, 'role_change', 'Assigned roles for user Prof. MECH 5', 'App\Models\User', 38, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.user_activity_logs VALUES (39, NULL, 'role_change', 'Assigned roles for user Prof. CS 1', 'App\Models\User', 39, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.user_activity_logs VALUES (40, NULL, 'role_change', 'Assigned roles for user Prof. CS 2', 'App\Models\User', 40, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.user_activity_logs VALUES (41, NULL, 'role_change', 'Assigned roles for user Prof. CS 3', 'App\Models\User', 41, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.user_activity_logs VALUES (42, NULL, 'role_change', 'Assigned roles for user Prof. ACCT 1', 'App\Models\User', 42, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.user_activity_logs VALUES (43, NULL, 'role_change', 'Assigned roles for user Prof. ACCT 2', 'App\Models\User', 43, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.user_activity_logs VALUES (44, NULL, 'role_change', 'Assigned roles for user Prof. ACCT 3', 'App\Models\User', 44, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.user_activity_logs VALUES (45, NULL, 'role_change', 'Assigned roles for user Prof. MGMT 1', 'App\Models\User', 45, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.user_activity_logs VALUES (46, NULL, 'role_change', 'Assigned roles for user Prof. MGMT 2', 'App\Models\User', 46, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.user_activity_logs VALUES (47, NULL, 'role_change', 'Assigned roles for user Prof. MGMT 3', 'App\Models\User', 47, '{"roles": ["Faculty"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'N01ICnofgYWBUz1wTl5mSziJWKXzZYR1dbnATUur', NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.user_activity_logs VALUES (48, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'XplX8zJk1exqi2xXOz21bAanHF8LGhUcGm77uddo', NULL, '2025-09-05 16:02:47', '2025-09-05 16:02:47', '2025-09-05 16:02:47');
INSERT INTO public.user_activity_logs VALUES (49, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'XplX8zJk1exqi2xXOz21bAanHF8LGhUcGm77uddo', NULL, '2025-09-05 16:29:50', '2025-09-05 16:29:50', '2025-09-05 16:29:50');
INSERT INTO public.user_activity_logs VALUES (50, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 22:29:41', '2025-09-05 22:29:41', '2025-09-05 22:29:41');
INSERT INTO public.user_activity_logs VALUES (51, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 22:44:00', '2025-09-05 22:44:00', '2025-09-05 22:44:00');
INSERT INTO public.user_activity_logs VALUES (52, 48, 'view', 'Viewed user profile: System Auditor', 'App\Models\User', 54, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 22:57:12', '2025-09-05 22:57:12', '2025-09-05 22:57:12');
INSERT INTO public.user_activity_logs VALUES (53, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 22:59:05', '2025-09-05 22:59:05', '2025-09-05 22:59:05');
INSERT INTO public.user_activity_logs VALUES (55, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 23:18:56', '2025-09-05 23:18:56', '2025-09-05 23:18:56');
INSERT INTO public.user_activity_logs VALUES (54, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 23:18:56', '2025-09-05 23:18:56', '2025-09-05 23:18:56');
INSERT INTO public.user_activity_logs VALUES (56, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 23:19:13', '2025-09-05 23:19:13', '2025-09-05 23:19:13');
INSERT INTO public.user_activity_logs VALUES (57, 48, 'view', 'Viewed user profile: System Auditor', 'App\Models\User', 54, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 23:36:18', '2025-09-05 23:36:18', '2025-09-05 23:36:18');
INSERT INTO public.user_activity_logs VALUES (58, NULL, 'role_change', 'Assigned roles for user Abe Liliane Funk', 'App\Models\User', 55, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:26', '2025-09-05 23:47:26', '2025-09-05 23:47:26');
INSERT INTO public.user_activity_logs VALUES (59, NULL, 'role_change', 'Assigned roles for user Robert James Johnson', 'App\Models\User', 56, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:26', '2025-09-05 23:47:26', '2025-09-05 23:47:26');
INSERT INTO public.user_activity_logs VALUES (60, NULL, 'role_change', 'Assigned roles for user Maria Isabel Garcia', 'App\Models\User', 57, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:26', '2025-09-05 23:47:26', '2025-09-05 23:47:26');
INSERT INTO public.user_activity_logs VALUES (61, NULL, 'role_change', 'Assigned roles for user David Lee Kim', 'App\Models\User', 58, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:26', '2025-09-05 23:47:26', '2025-09-05 23:47:26');
INSERT INTO public.user_activity_logs VALUES (62, NULL, 'role_change', 'Assigned roles for user Zakary Ruthie Miller', 'App\Models\User', 59, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:27', '2025-09-05 23:47:27', '2025-09-05 23:47:27');
INSERT INTO public.user_activity_logs VALUES (63, NULL, 'role_change', 'Assigned roles for user Jane Elizabeth Smith', 'App\Models\User', 60, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:27', '2025-09-05 23:47:27', '2025-09-05 23:47:27');
INSERT INTO public.user_activity_logs VALUES (64, NULL, 'role_change', 'Assigned roles for user Jared Gaston Bauch', 'App\Models\User', 61, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:27', '2025-09-05 23:47:27', '2025-09-05 23:47:27');
INSERT INTO public.user_activity_logs VALUES (65, NULL, 'role_change', 'Assigned roles for user Benedict  Emard', 'App\Models\User', 62, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:27', '2025-09-05 23:47:27', '2025-09-05 23:47:27');
INSERT INTO public.user_activity_logs VALUES (66, NULL, 'role_change', 'Assigned roles for user Magali  Barton', 'App\Models\User', 63, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:27', '2025-09-05 23:47:27', '2025-09-05 23:47:27');
INSERT INTO public.user_activity_logs VALUES (67, NULL, 'role_change', 'Assigned roles for user Michale  Gerlach', 'App\Models\User', 64, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:28', '2025-09-05 23:47:28', '2025-09-05 23:47:28');
INSERT INTO public.user_activity_logs VALUES (68, NULL, 'role_change', 'Assigned roles for user Kade  Hessel', 'App\Models\User', 65, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:28', '2025-09-05 23:47:28', '2025-09-05 23:47:28');
INSERT INTO public.user_activity_logs VALUES (69, NULL, 'role_change', 'Assigned roles for user Oliver  Nolan', 'App\Models\User', 66, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:28', '2025-09-05 23:47:28', '2025-09-05 23:47:28');
INSERT INTO public.user_activity_logs VALUES (70, NULL, 'role_change', 'Assigned roles for user Amir  Ziemann', 'App\Models\User', 67, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:28', '2025-09-05 23:47:28', '2025-09-05 23:47:28');
INSERT INTO public.user_activity_logs VALUES (71, NULL, 'role_change', 'Assigned roles for user Lelia Graciela Pollich', 'App\Models\User', 68, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:29', '2025-09-05 23:47:29', '2025-09-05 23:47:29');
INSERT INTO public.user_activity_logs VALUES (72, NULL, 'role_change', 'Assigned roles for user Nasir  O''Connell', 'App\Models\User', 69, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:29', '2025-09-05 23:47:29', '2025-09-05 23:47:29');
INSERT INTO public.user_activity_logs VALUES (73, NULL, 'role_change', 'Assigned roles for user John Michael Doe', 'App\Models\User', 70, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:29', '2025-09-05 23:47:29', '2025-09-05 23:47:29');
INSERT INTO public.user_activity_logs VALUES (74, NULL, 'role_change', 'Assigned roles for user Pete  Beier', 'App\Models\User', 71, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:29', '2025-09-05 23:47:29', '2025-09-05 23:47:29');
INSERT INTO public.user_activity_logs VALUES (75, NULL, 'role_change', 'Assigned roles for user Robert Easton Kautzer', 'App\Models\User', 72, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:30', '2025-09-05 23:47:30', '2025-09-05 23:47:30');
INSERT INTO public.user_activity_logs VALUES (76, NULL, 'role_change', 'Assigned roles for user Jailyn Patrick Grant', 'App\Models\User', 73, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:30', '2025-09-05 23:47:30', '2025-09-05 23:47:30');
INSERT INTO public.user_activity_logs VALUES (77, NULL, 'role_change', 'Assigned roles for user Vanessa Gina Heller', 'App\Models\User', 74, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:30', '2025-09-05 23:47:30', '2025-09-05 23:47:30');
INSERT INTO public.user_activity_logs VALUES (78, NULL, 'role_change', 'Assigned roles for user Dianna  Gleason', 'App\Models\User', 75, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:30', '2025-09-05 23:47:30', '2025-09-05 23:47:30');
INSERT INTO public.user_activity_logs VALUES (79, NULL, 'role_change', 'Assigned roles for user Candace Kane D''Amore', 'App\Models\User', 76, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:31', '2025-09-05 23:47:31', '2025-09-05 23:47:31');
INSERT INTO public.user_activity_logs VALUES (80, NULL, 'role_change', 'Assigned roles for user Leanne Jaida Crist', 'App\Models\User', 77, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:31', '2025-09-05 23:47:31', '2025-09-05 23:47:31');
INSERT INTO public.user_activity_logs VALUES (81, NULL, 'role_change', 'Assigned roles for user Lesley Ozella Ferry', 'App\Models\User', 78, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:31', '2025-09-05 23:47:31', '2025-09-05 23:47:31');
INSERT INTO public.user_activity_logs VALUES (82, NULL, 'role_change', 'Assigned roles for user Dallin Tressa Gibson', 'App\Models\User', 79, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:31', '2025-09-05 23:47:31', '2025-09-05 23:47:31');
INSERT INTO public.user_activity_logs VALUES (83, NULL, 'role_change', 'Assigned roles for user Maximus Rita Lueilwitz', 'App\Models\User', 80, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:32', '2025-09-05 23:47:32', '2025-09-05 23:47:32');
INSERT INTO public.user_activity_logs VALUES (84, NULL, 'role_change', 'Assigned roles for user Jaleel  Jones', 'App\Models\User', 81, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:32', '2025-09-05 23:47:32', '2025-09-05 23:47:32');
INSERT INTO public.user_activity_logs VALUES (85, NULL, 'role_change', 'Assigned roles for user Queen Michel Eichmann', 'App\Models\User', 82, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:32', '2025-09-05 23:47:32', '2025-09-05 23:47:32');
INSERT INTO public.user_activity_logs VALUES (86, NULL, 'role_change', 'Assigned roles for user Lacy  Senger', 'App\Models\User', 83, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:32', '2025-09-05 23:47:32', '2025-09-05 23:47:32');
INSERT INTO public.user_activity_logs VALUES (87, NULL, 'role_change', 'Assigned roles for user Lyric Bette Terry', 'App\Models\User', 84, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:33', '2025-09-05 23:47:33', '2025-09-05 23:47:33');
INSERT INTO public.user_activity_logs VALUES (88, NULL, 'role_change', 'Assigned roles for user Demetrius Ward Paucek', 'App\Models\User', 85, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:33', '2025-09-05 23:47:33', '2025-09-05 23:47:33');
INSERT INTO public.user_activity_logs VALUES (89, NULL, 'role_change', 'Assigned roles for user Micaela Wilber Koss', 'App\Models\User', 86, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:33', '2025-09-05 23:47:33', '2025-09-05 23:47:33');
INSERT INTO public.user_activity_logs VALUES (90, NULL, 'role_change', 'Assigned roles for user Ottilie  Flatley', 'App\Models\User', 87, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:33', '2025-09-05 23:47:33', '2025-09-05 23:47:33');
INSERT INTO public.user_activity_logs VALUES (91, NULL, 'role_change', 'Assigned roles for user Selmer Rowena Murray', 'App\Models\User', 88, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:34', '2025-09-05 23:47:34', '2025-09-05 23:47:34');
INSERT INTO public.user_activity_logs VALUES (92, NULL, 'role_change', 'Assigned roles for user Eve Nicolas Thiel', 'App\Models\User', 89, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:34', '2025-09-05 23:47:34', '2025-09-05 23:47:34');
INSERT INTO public.user_activity_logs VALUES (93, NULL, 'role_change', 'Assigned roles for user Laura Pat Altenwerth', 'App\Models\User', 90, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:34', '2025-09-05 23:47:34', '2025-09-05 23:47:34');
INSERT INTO public.user_activity_logs VALUES (94, NULL, 'role_change', 'Assigned roles for user Edmond Triston Kassulke', 'App\Models\User', 91, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:35', '2025-09-05 23:47:35', '2025-09-05 23:47:35');
INSERT INTO public.user_activity_logs VALUES (95, NULL, 'role_change', 'Assigned roles for user Elvis Jeanie Gusikowski', 'App\Models\User', 92, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:35', '2025-09-05 23:47:35', '2025-09-05 23:47:35');
INSERT INTO public.user_activity_logs VALUES (96, NULL, 'role_change', 'Assigned roles for user Rosalind Clifford Brown', 'App\Models\User', 93, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:35', '2025-09-05 23:47:35', '2025-09-05 23:47:35');
INSERT INTO public.user_activity_logs VALUES (97, NULL, 'role_change', 'Assigned roles for user Lexi  Swaniawski', 'App\Models\User', 94, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:35', '2025-09-05 23:47:35', '2025-09-05 23:47:35');
INSERT INTO public.user_activity_logs VALUES (98, NULL, 'role_change', 'Assigned roles for user Turner Audie Schinner', 'App\Models\User', 95, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:36', '2025-09-05 23:47:36', '2025-09-05 23:47:36');
INSERT INTO public.user_activity_logs VALUES (99, NULL, 'role_change', 'Assigned roles for user Shyann Rosalinda Toy', 'App\Models\User', 96, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:36', '2025-09-05 23:47:36', '2025-09-05 23:47:36');
INSERT INTO public.user_activity_logs VALUES (100, NULL, 'role_change', 'Assigned roles for user Dorris Velda Kutch', 'App\Models\User', 97, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:36', '2025-09-05 23:47:36', '2025-09-05 23:47:36');
INSERT INTO public.user_activity_logs VALUES (101, NULL, 'role_change', 'Assigned roles for user Nicholaus Roxanne Bednar', 'App\Models\User', 98, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:37', '2025-09-05 23:47:37', '2025-09-05 23:47:37');
INSERT INTO public.user_activity_logs VALUES (102, NULL, 'role_change', 'Assigned roles for user Greyson Edyth Veum', 'App\Models\User', 99, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:37', '2025-09-05 23:47:37', '2025-09-05 23:47:37');
INSERT INTO public.user_activity_logs VALUES (103, NULL, 'role_change', 'Assigned roles for user Kurtis  McCullough', 'App\Models\User', 100, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:37', '2025-09-05 23:47:37', '2025-09-05 23:47:37');
INSERT INTO public.user_activity_logs VALUES (104, NULL, 'role_change', 'Assigned roles for user Chandler Adriel Hoppe', 'App\Models\User', 101, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:37', '2025-09-05 23:47:37', '2025-09-05 23:47:37');
INSERT INTO public.user_activity_logs VALUES (105, NULL, 'role_change', 'Assigned roles for user Antwan Judge Hodkiewicz', 'App\Models\User', 102, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:38', '2025-09-05 23:47:38', '2025-09-05 23:47:38');
INSERT INTO public.user_activity_logs VALUES (106, NULL, 'role_change', 'Assigned roles for user Kianna  Bahringer', 'App\Models\User', 103, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:38', '2025-09-05 23:47:38', '2025-09-05 23:47:38');
INSERT INTO public.user_activity_logs VALUES (107, NULL, 'role_change', 'Assigned roles for user Audie Rae Schuppe', 'App\Models\User', 104, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:38', '2025-09-05 23:47:38', '2025-09-05 23:47:38');
INSERT INTO public.user_activity_logs VALUES (108, NULL, 'role_change', 'Assigned roles for user Mittie  Mayer', 'App\Models\User', 105, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:38', '2025-09-05 23:47:38', '2025-09-05 23:47:38');
INSERT INTO public.user_activity_logs VALUES (109, NULL, 'role_change', 'Assigned roles for user Robyn Colt Beatty', 'App\Models\User', 106, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:39', '2025-09-05 23:47:39', '2025-09-05 23:47:39');
INSERT INTO public.user_activity_logs VALUES (110, NULL, 'role_change', 'Assigned roles for user Hilario Mekhi Schuppe', 'App\Models\User', 107, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:39', '2025-09-05 23:47:39', '2025-09-05 23:47:39');
INSERT INTO public.user_activity_logs VALUES (111, NULL, 'role_change', 'Assigned roles for user Syble  Fahey', 'App\Models\User', 108, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:39', '2025-09-05 23:47:39', '2025-09-05 23:47:39');
INSERT INTO public.user_activity_logs VALUES (112, NULL, 'role_change', 'Assigned roles for user Cydney Birdie Carter', 'App\Models\User', 109, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:39', '2025-09-05 23:47:39', '2025-09-05 23:47:39');
INSERT INTO public.user_activity_logs VALUES (113, NULL, 'role_change', 'Assigned roles for user Bruce Guiseppe Renner', 'App\Models\User', 110, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:40', '2025-09-05 23:47:40', '2025-09-05 23:47:40');
INSERT INTO public.user_activity_logs VALUES (114, NULL, 'role_change', 'Assigned roles for user Frank  Howe', 'App\Models\User', 111, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:40', '2025-09-05 23:47:40', '2025-09-05 23:47:40');
INSERT INTO public.user_activity_logs VALUES (115, NULL, 'role_change', 'Assigned roles for user Alfred Guiseppe Jerde', 'App\Models\User', 112, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:40', '2025-09-05 23:47:40', '2025-09-05 23:47:40');
INSERT INTO public.user_activity_logs VALUES (116, NULL, 'role_change', 'Assigned roles for user Amely Otho Zulauf', 'App\Models\User', 113, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:40', '2025-09-05 23:47:40', '2025-09-05 23:47:40');
INSERT INTO public.user_activity_logs VALUES (117, NULL, 'role_change', 'Assigned roles for user Anna Aubrey Rutherford', 'App\Models\User', 114, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:41', '2025-09-05 23:47:41', '2025-09-05 23:47:41');
INSERT INTO public.user_activity_logs VALUES (118, NULL, 'role_change', 'Assigned roles for user Kory  Murray', 'App\Models\User', 115, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:41', '2025-09-05 23:47:41', '2025-09-05 23:47:41');
INSERT INTO public.user_activity_logs VALUES (119, NULL, 'role_change', 'Assigned roles for user Maeve Madge Thiel', 'App\Models\User', 116, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:41', '2025-09-05 23:47:41', '2025-09-05 23:47:41');
INSERT INTO public.user_activity_logs VALUES (120, NULL, 'role_change', 'Assigned roles for user Kiarra Theodore Roob', 'App\Models\User', 117, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:41', '2025-09-05 23:47:41', '2025-09-05 23:47:41');
INSERT INTO public.user_activity_logs VALUES (121, NULL, 'role_change', 'Assigned roles for user Gino Heber Mitchell', 'App\Models\User', 118, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:42', '2025-09-05 23:47:42', '2025-09-05 23:47:42');
INSERT INTO public.user_activity_logs VALUES (122, NULL, 'role_change', 'Assigned roles for user Briana Kennith Leffler', 'App\Models\User', 119, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:42', '2025-09-05 23:47:42', '2025-09-05 23:47:42');
INSERT INTO public.user_activity_logs VALUES (123, NULL, 'role_change', 'Assigned roles for user Triston April Botsford', 'App\Models\User', 120, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:42', '2025-09-05 23:47:42', '2025-09-05 23:47:42');
INSERT INTO public.user_activity_logs VALUES (124, NULL, 'role_change', 'Assigned roles for user Jeramie Joannie Funk', 'App\Models\User', 121, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:43', '2025-09-05 23:47:43', '2025-09-05 23:47:43');
INSERT INTO public.user_activity_logs VALUES (125, NULL, 'role_change', 'Assigned roles for user Dejah  Schimmel', 'App\Models\User', 122, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:43', '2025-09-05 23:47:43', '2025-09-05 23:47:43');
INSERT INTO public.user_activity_logs VALUES (126, NULL, 'role_change', 'Assigned roles for user Kamryn Jason Stoltenberg', 'App\Models\User', 123, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:43', '2025-09-05 23:47:43', '2025-09-05 23:47:43');
INSERT INTO public.user_activity_logs VALUES (127, NULL, 'role_change', 'Assigned roles for user Agustin Sharon Halvorson', 'App\Models\User', 124, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:44', '2025-09-05 23:47:44', '2025-09-05 23:47:44');
INSERT INTO public.user_activity_logs VALUES (128, NULL, 'role_change', 'Assigned roles for user Kennedy Vivienne Marvin', 'App\Models\User', 125, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:44', '2025-09-05 23:47:44', '2025-09-05 23:47:44');
INSERT INTO public.user_activity_logs VALUES (129, NULL, 'role_change', 'Assigned roles for user Agnes Ariane Schinner', 'App\Models\User', 126, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:44', '2025-09-05 23:47:44', '2025-09-05 23:47:44');
INSERT INTO public.user_activity_logs VALUES (130, NULL, 'role_change', 'Assigned roles for user Loyce Janice Pollich', 'App\Models\User', 127, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:44', '2025-09-05 23:47:44', '2025-09-05 23:47:44');
INSERT INTO public.user_activity_logs VALUES (131, NULL, 'role_change', 'Assigned roles for user Lillie Arvel Crona', 'App\Models\User', 128, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:45', '2025-09-05 23:47:45', '2025-09-05 23:47:45');
INSERT INTO public.user_activity_logs VALUES (132, NULL, 'role_change', 'Assigned roles for user Cornelius Icie Heidenreich', 'App\Models\User', 129, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:45', '2025-09-05 23:47:45', '2025-09-05 23:47:45');
INSERT INTO public.user_activity_logs VALUES (133, NULL, 'role_change', 'Assigned roles for user Stewart Helen Paucek', 'App\Models\User', 130, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:45', '2025-09-05 23:47:45', '2025-09-05 23:47:45');
INSERT INTO public.user_activity_logs VALUES (134, NULL, 'role_change', 'Assigned roles for user Alyson Westley Jacobs', 'App\Models\User', 131, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:45', '2025-09-05 23:47:45', '2025-09-05 23:47:45');
INSERT INTO public.user_activity_logs VALUES (135, NULL, 'role_change', 'Assigned roles for user Shaniya Jeffrey Spinka', 'App\Models\User', 132, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:46', '2025-09-05 23:47:46', '2025-09-05 23:47:46');
INSERT INTO public.user_activity_logs VALUES (136, NULL, 'role_change', 'Assigned roles for user Herta Bill Prohaska', 'App\Models\User', 133, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:46', '2025-09-05 23:47:46', '2025-09-05 23:47:46');
INSERT INTO public.user_activity_logs VALUES (137, NULL, 'role_change', 'Assigned roles for user Hans Rebeka Flatley', 'App\Models\User', 134, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:46', '2025-09-05 23:47:46', '2025-09-05 23:47:46');
INSERT INTO public.user_activity_logs VALUES (138, NULL, 'role_change', 'Assigned roles for user Brenda Stuart Wunsch', 'App\Models\User', 135, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:46', '2025-09-05 23:47:46', '2025-09-05 23:47:46');
INSERT INTO public.user_activity_logs VALUES (139, NULL, 'role_change', 'Assigned roles for user Birdie Beau Wuckert', 'App\Models\User', 136, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:47', '2025-09-05 23:47:47', '2025-09-05 23:47:47');
INSERT INTO public.user_activity_logs VALUES (140, NULL, 'role_change', 'Assigned roles for user Coby Faye Schuppe', 'App\Models\User', 137, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:47', '2025-09-05 23:47:47', '2025-09-05 23:47:47');
INSERT INTO public.user_activity_logs VALUES (141, NULL, 'role_change', 'Assigned roles for user Nina Weston Hirthe', 'App\Models\User', 138, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:47', '2025-09-05 23:47:47', '2025-09-05 23:47:47');
INSERT INTO public.user_activity_logs VALUES (142, NULL, 'role_change', 'Assigned roles for user Leilani Marianne Russel', 'App\Models\User', 139, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:48', '2025-09-05 23:47:48', '2025-09-05 23:47:48');
INSERT INTO public.user_activity_logs VALUES (143, NULL, 'role_change', 'Assigned roles for user Marianna Cali Schultz', 'App\Models\User', 140, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:48', '2025-09-05 23:47:48', '2025-09-05 23:47:48');
INSERT INTO public.user_activity_logs VALUES (144, NULL, 'role_change', 'Assigned roles for user Elenor Susanna Klocko', 'App\Models\User', 141, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:48', '2025-09-05 23:47:48', '2025-09-05 23:47:48');
INSERT INTO public.user_activity_logs VALUES (145, NULL, 'role_change', 'Assigned roles for user Madaline Gladyce Pfannerstill', 'App\Models\User', 142, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:48', '2025-09-05 23:47:48', '2025-09-05 23:47:48');
INSERT INTO public.user_activity_logs VALUES (146, NULL, 'role_change', 'Assigned roles for user Janis Eugene Windler', 'App\Models\User', 143, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:49', '2025-09-05 23:47:49', '2025-09-05 23:47:49');
INSERT INTO public.user_activity_logs VALUES (147, NULL, 'role_change', 'Assigned roles for user Jacynthe Adelbert Hoeger', 'App\Models\User', 144, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:49', '2025-09-05 23:47:49', '2025-09-05 23:47:49');
INSERT INTO public.user_activity_logs VALUES (148, NULL, 'role_change', 'Assigned roles for user Delta  Ankunding', 'App\Models\User', 145, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:49', '2025-09-05 23:47:49', '2025-09-05 23:47:49');
INSERT INTO public.user_activity_logs VALUES (149, NULL, 'role_change', 'Assigned roles for user Mozell Reggie Gislason', 'App\Models\User', 146, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:49', '2025-09-05 23:47:49', '2025-09-05 23:47:49');
INSERT INTO public.user_activity_logs VALUES (150, NULL, 'role_change', 'Assigned roles for user Casimir  Ondricka', 'App\Models\User', 147, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:50', '2025-09-05 23:47:50', '2025-09-05 23:47:50');
INSERT INTO public.user_activity_logs VALUES (151, NULL, 'role_change', 'Assigned roles for user Cecelia Destinee Murazik', 'App\Models\User', 148, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:50', '2025-09-05 23:47:50', '2025-09-05 23:47:50');
INSERT INTO public.user_activity_logs VALUES (152, NULL, 'role_change', 'Assigned roles for user Concepcion  Shields', 'App\Models\User', 149, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:50', '2025-09-05 23:47:50', '2025-09-05 23:47:50');
INSERT INTO public.user_activity_logs VALUES (153, NULL, 'role_change', 'Assigned roles for user Jaylin  Lynch', 'App\Models\User', 150, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:50', '2025-09-05 23:47:50', '2025-09-05 23:47:50');
INSERT INTO public.user_activity_logs VALUES (154, NULL, 'role_change', 'Assigned roles for user Eldora Matilda King', 'App\Models\User', 151, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:51', '2025-09-05 23:47:51', '2025-09-05 23:47:51');
INSERT INTO public.user_activity_logs VALUES (155, NULL, 'role_change', 'Assigned roles for user Chloe Toy Quigley', 'App\Models\User', 152, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:51', '2025-09-05 23:47:51', '2025-09-05 23:47:51');
INSERT INTO public.user_activity_logs VALUES (156, NULL, 'role_change', 'Assigned roles for user Melvin Lula Orn', 'App\Models\User', 153, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:51', '2025-09-05 23:47:51', '2025-09-05 23:47:51');
INSERT INTO public.user_activity_logs VALUES (157, NULL, 'role_change', 'Assigned roles for user Elise Sister Nikolaus', 'App\Models\User', 154, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'iLeNlWWCn84oRstp1GOtoxwjtADWP2yRkcMvjfXO', NULL, '2025-09-05 23:47:51', '2025-09-05 23:47:51', '2025-09-05 23:47:51');
INSERT INTO public.user_activity_logs VALUES (158, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 23:48:50', '2025-09-05 23:48:50', '2025-09-05 23:48:50');
INSERT INTO public.user_activity_logs VALUES (159, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 23:49:19', '2025-09-05 23:49:19', '2025-09-05 23:49:19');
INSERT INTO public.user_activity_logs VALUES (160, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 23:49:27', '2025-09-05 23:49:27', '2025-09-05 23:49:27');
INSERT INTO public.user_activity_logs VALUES (161, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-05 23:49:47', '2025-09-05 23:49:47', '2025-09-05 23:49:47');
INSERT INTO public.user_activity_logs VALUES (162, 48, 'view', 'Viewed user profile: John Michael Doe', 'App\Models\User', 70, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-06 00:02:17', '2025-09-06 00:02:17', '2025-09-06 00:02:17');
INSERT INTO public.user_activity_logs VALUES (163, 48, 'view', 'Viewed user profile: John Michael Doe', 'App\Models\User', 70, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-06 00:11:34', '2025-09-06 00:11:34', '2025-09-06 00:11:34');
INSERT INTO public.user_activity_logs VALUES (164, NULL, 'role_change', 'Assigned roles for user Test  Student', 'App\Models\User', 155, '{"roles": ["Student"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'MMDvgwOVu3XiCgbXmF0xk8xclQupqjgKaLMqTphd', NULL, '2025-09-06 00:39:34', '2025-09-06 00:39:34', '2025-09-06 00:39:34');
INSERT INTO public.user_activity_logs VALUES (165, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-06 00:40:30', '2025-09-06 00:40:30', '2025-09-06 00:40:30');
INSERT INTO public.user_activity_logs VALUES (166, 48, 'update', 'Updated user: Test  Student', 'App\Models\User', 155, '{"password": "Changed", "date_of_birth": {"new": "2000-01-01", "old": "2000-01-01T00:00:00.000000Z"}}', '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-06 00:41:28', '2025-09-06 00:41:28', '2025-09-06 00:41:28');
INSERT INTO public.user_activity_logs VALUES (167, 48, 'view', 'Viewed user profile: Test  Student', 'App\Models\User', 155, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-06 00:41:37', '2025-09-06 00:41:37', '2025-09-06 00:41:37');
INSERT INTO public.user_activity_logs VALUES (168, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Db8JSHeV1tlySKajM2jjP36kTDkCUDScGrWsU0O1', NULL, '2025-09-06 03:19:38', '2025-09-06 03:19:38', '2025-09-06 03:19:38');
INSERT INTO public.user_activity_logs VALUES (169, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'pwOxQ06acUXpMkCp56UVq0O3Ux5LvoFZRAJj4OxH', NULL, '2025-09-06 18:16:54', '2025-09-06 18:16:54', '2025-09-06 18:16:54');
INSERT INTO public.user_activity_logs VALUES (170, 48, 'view', 'Viewed user profile: Test  Student', 'App\Models\User', 155, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'pwOxQ06acUXpMkCp56UVq0O3Ux5LvoFZRAJj4OxH', NULL, '2025-09-06 18:17:15', '2025-09-06 18:17:15', '2025-09-06 18:17:15');
INSERT INTO public.user_activity_logs VALUES (171, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'BwC4w8Rpf9wYwtoNcGy6ZbSZSUr09kEo7Qc77qQJ', NULL, '2025-09-07 05:06:55', '2025-09-07 05:06:55', '2025-09-07 05:06:55');
INSERT INTO public.user_activity_logs VALUES (172, 48, 'view', 'Viewed user profile: Test  Student', 'App\Models\User', 155, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'BwC4w8Rpf9wYwtoNcGy6ZbSZSUr09kEo7Qc77qQJ', NULL, '2025-09-07 05:07:08', '2025-09-07 05:07:08', '2025-09-07 05:07:08');
INSERT INTO public.user_activity_logs VALUES (173, 155, 'password_reset_completed', 'User Test  Student completed password reset', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'BwC4w8Rpf9wYwtoNcGy6ZbSZSUr09kEo7Qc77qQJ', NULL, '2025-09-07 05:07:50', '2025-09-07 05:07:50', '2025-09-07 05:07:50');
INSERT INTO public.user_activity_logs VALUES (174, 48, 'view', 'Viewed user profile: Test  Student', 'App\Models\User', 155, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'BwC4w8Rpf9wYwtoNcGy6ZbSZSUr09kEo7Qc77qQJ', NULL, '2025-09-07 05:07:53', '2025-09-07 05:07:53', '2025-09-07 05:07:53');
INSERT INTO public.user_activity_logs VALUES (175, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '1wPnXx8tfFf3qecz0ey4H6jMh3jfJgVZTrzHd7Qr', NULL, '2025-09-07 10:39:15', '2025-09-07 10:39:15', '2025-09-07 10:39:15');
INSERT INTO public.user_activity_logs VALUES (176, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '1wPnXx8tfFf3qecz0ey4H6jMh3jfJgVZTrzHd7Qr', NULL, '2025-09-07 10:39:58', '2025-09-07 10:39:58', '2025-09-07 10:39:58');
INSERT INTO public.user_activity_logs VALUES (177, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '1wPnXx8tfFf3qecz0ey4H6jMh3jfJgVZTrzHd7Qr', NULL, '2025-09-07 10:40:17', '2025-09-07 10:40:17', '2025-09-07 10:40:17');
INSERT INTO public.user_activity_logs VALUES (178, 48, 'view', 'Viewed user profile: Super Administrator', 'App\Models\User', 48, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '1wPnXx8tfFf3qecz0ey4H6jMh3jfJgVZTrzHd7Qr', NULL, '2025-09-07 10:40:20', '2025-09-07 10:40:20', '2025-09-07 10:40:20');
INSERT INTO public.user_activity_logs VALUES (179, 48, 'view', 'Viewed user profile: Super Administrator', 'App\Models\User', 48, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '1wPnXx8tfFf3qecz0ey4H6jMh3jfJgVZTrzHd7Qr', NULL, '2025-09-07 10:41:19', '2025-09-07 10:41:19', '2025-09-07 10:41:19');
INSERT INTO public.user_activity_logs VALUES (180, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Dhc9YehFqsaXDXM5MYQ6nYR5zbhZ9EPe16cuIKNh', NULL, '2025-09-07 15:54:22', '2025-09-07 15:54:22', '2025-09-07 15:54:22');
INSERT INTO public.user_activity_logs VALUES (181, 48, 'view', 'Viewed user profile: Super Administrator', 'App\Models\User', 48, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Dhc9YehFqsaXDXM5MYQ6nYR5zbhZ9EPe16cuIKNh', NULL, '2025-09-07 15:54:28', '2025-09-07 15:54:28', '2025-09-07 15:54:28');
INSERT INTO public.user_activity_logs VALUES (182, 48, 'view', 'Viewed user profile: Super Administrator', 'App\Models\User', 48, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'PuXuWpU0E1oBm7qX79bJNPYLFWBjoCo9co3B2N1B', NULL, '2025-09-08 01:11:18', '2025-09-08 01:11:18', '2025-09-08 01:11:18');
INSERT INTO public.user_activity_logs VALUES (183, 48, 'view', 'Viewed user profile: Super Administrator', 'App\Models\User', 48, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'PuXuWpU0E1oBm7qX79bJNPYLFWBjoCo9co3B2N1B', NULL, '2025-09-08 01:11:35', '2025-09-08 01:11:35', '2025-09-08 01:11:35');
INSERT INTO public.user_activity_logs VALUES (184, 48, 'view', 'Viewed user profile: Super Administrator', 'App\Models\User', 48, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'PuXuWpU0E1oBm7qX79bJNPYLFWBjoCo9co3B2N1B', NULL, '2025-09-08 01:19:33', '2025-09-08 01:19:33', '2025-09-08 01:19:33');
INSERT INTO public.user_activity_logs VALUES (185, 48, 'view', 'Viewed user profile: Super Administrator', 'App\Models\User', 48, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'PuXuWpU0E1oBm7qX79bJNPYLFWBjoCo9co3B2N1B', NULL, '2025-09-08 01:25:37', '2025-09-08 01:25:37', '2025-09-08 01:25:37');
INSERT INTO public.user_activity_logs VALUES (218, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'jP0jPFSmIOjiJXGR6rYHi2aatfiiBPFDB0davsyZ', NULL, '2025-09-10 11:05:24', '2025-09-10 11:05:24', '2025-09-10 11:05:24');
INSERT INTO public.user_activity_logs VALUES (219, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'jP0jPFSmIOjiJXGR6rYHi2aatfiiBPFDB0davsyZ', NULL, '2025-09-10 11:05:53', '2025-09-10 11:05:53', '2025-09-10 11:05:53');
INSERT INTO public.user_activity_logs VALUES (220, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'jP0jPFSmIOjiJXGR6rYHi2aatfiiBPFDB0davsyZ', NULL, '2025-09-10 12:34:30', '2025-09-10 12:34:30', '2025-09-10 12:34:30');
INSERT INTO public.user_activity_logs VALUES (221, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'jP0jPFSmIOjiJXGR6rYHi2aatfiiBPFDB0davsyZ', NULL, '2025-09-10 12:34:44', '2025-09-10 12:34:44', '2025-09-10 12:34:44');
INSERT INTO public.user_activity_logs VALUES (222, 48, 'view', 'Viewed user profile: Mozell Reggie Gislason', 'App\Models\User', 146, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'jP0jPFSmIOjiJXGR6rYHi2aatfiiBPFDB0davsyZ', NULL, '2025-09-10 12:34:56', '2025-09-10 12:34:56', '2025-09-10 12:34:56');
INSERT INTO public.user_activity_logs VALUES (223, 146, 'password_reset_completed', 'User Mozell Reggie Gislason completed password reset', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'jP0jPFSmIOjiJXGR6rYHi2aatfiiBPFDB0davsyZ', NULL, '2025-09-10 12:35:21', '2025-09-10 12:35:21', '2025-09-10 12:35:21');
INSERT INTO public.user_activity_logs VALUES (224, 48, 'view', 'Viewed user profile: Mozell Reggie Gislason', 'App\Models\User', 146, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'jP0jPFSmIOjiJXGR6rYHi2aatfiiBPFDB0davsyZ', NULL, '2025-09-10 12:35:23', '2025-09-10 12:35:23', '2025-09-10 12:35:23');
INSERT INTO public.user_activity_logs VALUES (225, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'TydM6o9iWxEPDcC70XchrK16ZlQSOO2b2RpB0hFp', NULL, '2025-09-16 10:01:08', '2025-09-16 10:01:08', '2025-09-16 10:01:08');
INSERT INTO public.user_activity_logs VALUES (226, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'TydM6o9iWxEPDcC70XchrK16ZlQSOO2b2RpB0hFp', NULL, '2025-09-16 10:01:12', '2025-09-16 10:01:12', '2025-09-16 10:01:12');
INSERT INTO public.user_activity_logs VALUES (227, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'TydM6o9iWxEPDcC70XchrK16ZlQSOO2b2RpB0hFp', NULL, '2025-09-16 11:19:35', '2025-09-16 11:19:35', '2025-09-16 11:19:35');
INSERT INTO public.user_activity_logs VALUES (229, NULL, 'role_change', 'Assigned roles for user Cassandre Hayes', 'App\Models\User', 175, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:42', '2025-09-17 15:38:42', '2025-09-17 15:38:42');
INSERT INTO public.user_activity_logs VALUES (230, NULL, 'role_change', 'Assigned roles for user Jaiden Watsica', 'App\Models\User', 176, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:43', '2025-09-17 15:38:43', '2025-09-17 15:38:43');
INSERT INTO public.user_activity_logs VALUES (231, NULL, 'role_change', 'Assigned roles for user Autumn Heathcote', 'App\Models\User', 177, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:43', '2025-09-17 15:38:43', '2025-09-17 15:38:43');
INSERT INTO public.user_activity_logs VALUES (232, NULL, 'role_change', 'Assigned roles for user Kory Mueller', 'App\Models\User', 178, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:43', '2025-09-17 15:38:43', '2025-09-17 15:38:43');
INSERT INTO public.user_activity_logs VALUES (233, NULL, 'role_change', 'Assigned roles for user Arianna Schuppe', 'App\Models\User', 179, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:44', '2025-09-17 15:38:44', '2025-09-17 15:38:44');
INSERT INTO public.user_activity_logs VALUES (234, NULL, 'role_change', 'Assigned roles for user Jacques Reilly', 'App\Models\User', 180, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:44', '2025-09-17 15:38:44', '2025-09-17 15:38:44');
INSERT INTO public.user_activity_logs VALUES (235, NULL, 'role_change', 'Assigned roles for user Tiara McLaughlin', 'App\Models\User', 181, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:44', '2025-09-17 15:38:44', '2025-09-17 15:38:44');
INSERT INTO public.user_activity_logs VALUES (236, NULL, 'role_change', 'Assigned roles for user Maximo Hauck', 'App\Models\User', 182, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:45', '2025-09-17 15:38:45', '2025-09-17 15:38:45');
INSERT INTO public.user_activity_logs VALUES (237, NULL, 'role_change', 'Assigned roles for user Vivienne Rippin', 'App\Models\User', 183, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:45', '2025-09-17 15:38:45', '2025-09-17 15:38:45');
INSERT INTO public.user_activity_logs VALUES (238, NULL, 'role_change', 'Assigned roles for user Axel Spencer', 'App\Models\User', 184, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:45', '2025-09-17 15:38:45', '2025-09-17 15:38:45');
INSERT INTO public.user_activity_logs VALUES (239, NULL, 'role_change', 'Assigned roles for user Helen Abshire', 'App\Models\User', 185, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:45', '2025-09-17 15:38:45', '2025-09-17 15:38:45');
INSERT INTO public.user_activity_logs VALUES (240, NULL, 'role_change', 'Assigned roles for user Hadley Zulauf', 'App\Models\User', 186, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:46', '2025-09-17 15:38:46', '2025-09-17 15:38:46');
INSERT INTO public.user_activity_logs VALUES (241, NULL, 'role_change', 'Assigned roles for user Lura Bashirian', 'App\Models\User', 187, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:46', '2025-09-17 15:38:46', '2025-09-17 15:38:46');
INSERT INTO public.user_activity_logs VALUES (242, NULL, 'role_change', 'Assigned roles for user Colt Bashirian', 'App\Models\User', 188, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:46', '2025-09-17 15:38:46', '2025-09-17 15:38:46');
INSERT INTO public.user_activity_logs VALUES (243, NULL, 'role_change', 'Assigned roles for user Chet Considine', 'App\Models\User', 189, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:46', '2025-09-17 15:38:46', '2025-09-17 15:38:46');
INSERT INTO public.user_activity_logs VALUES (244, NULL, 'role_change', 'Assigned roles for user Rosario Lowe', 'App\Models\User', 190, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:46', '2025-09-17 15:38:46', '2025-09-17 15:38:46');
INSERT INTO public.user_activity_logs VALUES (245, NULL, 'role_change', 'Assigned roles for user Arden Kessler', 'App\Models\User', 191, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:47', '2025-09-17 15:38:47', '2025-09-17 15:38:47');
INSERT INTO public.user_activity_logs VALUES (246, NULL, 'role_change', 'Assigned roles for user Alda Hand', 'App\Models\User', 192, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:47', '2025-09-17 15:38:47', '2025-09-17 15:38:47');
INSERT INTO public.user_activity_logs VALUES (247, NULL, 'role_change', 'Assigned roles for user Luciano Schneider', 'App\Models\User', 193, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:47', '2025-09-17 15:38:47', '2025-09-17 15:38:47');
INSERT INTO public.user_activity_logs VALUES (248, NULL, 'role_change', 'Assigned roles for user Clay Schmidt', 'App\Models\User', 194, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:47', '2025-09-17 15:38:47', '2025-09-17 15:38:47');
INSERT INTO public.user_activity_logs VALUES (249, NULL, 'role_change', 'Assigned roles for user Celia Kshlerin', 'App\Models\User', 195, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:48', '2025-09-17 15:38:48', '2025-09-17 15:38:48');
INSERT INTO public.user_activity_logs VALUES (250, NULL, 'role_change', 'Assigned roles for user Cali Kovacek', 'App\Models\User', 196, '{"roles": ["Applicant"], "action": "Assigned"}', '127.0.0.1', 'Symfony', 'NKtNmQ08yqClMlBCZKAMbdyI6wrzkpUJFe1jUA0i', NULL, '2025-09-17 15:38:48', '2025-09-17 15:38:48', '2025-09-17 15:38:48');
INSERT INTO public.user_activity_logs VALUES (251, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'mS0czuAedjUaBs8io20OEiNIEP6tasQCBCBfRXDC', NULL, '2025-09-20 23:15:42', '2025-09-20 23:15:42', '2025-09-20 23:15:42');
INSERT INTO public.user_activity_logs VALUES (252, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'mS0czuAedjUaBs8io20OEiNIEP6tasQCBCBfRXDC', NULL, '2025-09-21 00:32:38', '2025-09-21 00:32:38', '2025-09-21 00:32:38');
INSERT INTO public.user_activity_logs VALUES (253, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'mS0czuAedjUaBs8io20OEiNIEP6tasQCBCBfRXDC', NULL, '2025-09-21 00:37:28', '2025-09-21 00:37:28', '2025-09-21 00:37:28');
INSERT INTO public.user_activity_logs VALUES (254, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'mS0czuAedjUaBs8io20OEiNIEP6tasQCBCBfRXDC', NULL, '2025-09-21 00:46:30', '2025-09-21 00:46:30', '2025-09-21 00:46:30');
INSERT INTO public.user_activity_logs VALUES (255, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'mS0czuAedjUaBs8io20OEiNIEP6tasQCBCBfRXDC', NULL, '2025-09-21 01:15:55', '2025-09-21 01:15:55', '2025-09-21 01:15:55');
INSERT INTO public.user_activity_logs VALUES (256, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'BUZotPpUos18ZKlh8MrqcNSpFTndNatBmX11Bt1a', NULL, '2025-09-21 11:51:16', '2025-09-21 11:51:16', '2025-09-21 11:51:16');
INSERT INTO public.user_activity_logs VALUES (257, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'BUZotPpUos18ZKlh8MrqcNSpFTndNatBmX11Bt1a', NULL, '2025-09-21 11:51:41', '2025-09-21 11:51:41', '2025-09-21 11:51:41');
INSERT INTO public.user_activity_logs VALUES (258, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'idLDkkbiTZdiXr4ZExgFpiOqaFcJN7RRVYo4Jlwx', NULL, '2025-09-21 15:29:08', '2025-09-21 15:29:08', '2025-09-21 15:29:08');
INSERT INTO public.user_activity_logs VALUES (259, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'idLDkkbiTZdiXr4ZExgFpiOqaFcJN7RRVYo4Jlwx', NULL, '2025-09-21 15:32:54', '2025-09-21 15:32:54', '2025-09-21 15:32:54');
INSERT INTO public.user_activity_logs VALUES (260, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'idLDkkbiTZdiXr4ZExgFpiOqaFcJN7RRVYo4Jlwx', NULL, '2025-09-21 15:34:49', '2025-09-21 15:34:49', '2025-09-21 15:34:49');
INSERT INTO public.user_activity_logs VALUES (261, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'idLDkkbiTZdiXr4ZExgFpiOqaFcJN7RRVYo4Jlwx', NULL, '2025-09-21 15:36:53', '2025-09-21 15:36:53', '2025-09-21 15:36:53');
INSERT INTO public.user_activity_logs VALUES (262, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'idLDkkbiTZdiXr4ZExgFpiOqaFcJN7RRVYo4Jlwx', NULL, '2025-09-21 15:37:07', '2025-09-21 15:37:07', '2025-09-21 15:37:07');
INSERT INTO public.user_activity_logs VALUES (263, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'idLDkkbiTZdiXr4ZExgFpiOqaFcJN7RRVYo4Jlwx', NULL, '2025-09-21 15:37:26', '2025-09-21 15:37:26', '2025-09-21 15:37:26');
INSERT INTO public.user_activity_logs VALUES (264, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'idLDkkbiTZdiXr4ZExgFpiOqaFcJN7RRVYo4Jlwx', NULL, '2025-09-21 15:37:55', '2025-09-21 15:37:55', '2025-09-21 15:37:55');
INSERT INTO public.user_activity_logs VALUES (265, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'ddLt2G9e91SECWhP5PqBD1qx31PmojaBVOVOFB9d', NULL, '2025-09-25 20:48:09', '2025-09-25 20:48:09', '2025-09-25 20:48:09');
INSERT INTO public.user_activity_logs VALUES (266, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'ddLt2G9e91SECWhP5PqBD1qx31PmojaBVOVOFB9d', NULL, '2025-09-25 20:51:22', '2025-09-25 20:51:22', '2025-09-25 20:51:22');
INSERT INTO public.user_activity_logs VALUES (267, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'Bie23UjbU92bwAXx70u3B0ZuMSZ02N8gD7RpnbTS', NULL, '2025-09-26 09:22:13', '2025-09-26 09:22:13', '2025-09-26 09:22:13');
INSERT INTO public.user_activity_logs VALUES (268, 48, 'view', 'Viewed user list', NULL, NULL, NULL, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'Bie23UjbU92bwAXx70u3B0ZuMSZ02N8gD7RpnbTS', NULL, '2025-09-26 11:57:51', '2025-09-26 11:57:51', '2025-09-26 11:57:51');


--
-- Data for Name: user_department_affiliations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.user_department_affiliations VALUES (1, 19, 4, 'primary', 'faculty', 100.00, '2023-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:52', '2025-09-04 22:28:52');
INSERT INTO public.user_department_affiliations VALUES (2, 20, 4, 'primary', 'faculty', 100.00, '2022-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.user_department_affiliations VALUES (3, 21, 4, 'primary', 'faculty', 100.00, '2016-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.user_department_affiliations VALUES (4, 22, 5, 'primary', 'faculty', 100.00, '2022-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.user_department_affiliations VALUES (5, 23, 5, 'primary', 'faculty', 100.00, '2022-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:53', '2025-09-04 22:28:53');
INSERT INTO public.user_department_affiliations VALUES (6, 24, 5, 'primary', 'faculty', 100.00, '2017-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.user_department_affiliations VALUES (7, 25, 5, 'primary', 'faculty', 100.00, '2023-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.user_department_affiliations VALUES (8, 26, 6, 'primary', 'faculty', 100.00, '2016-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.user_department_affiliations VALUES (9, 27, 6, 'primary', 'faculty', 100.00, '2021-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:54', '2025-09-04 22:28:54');
INSERT INTO public.user_department_affiliations VALUES (10, 28, 6, 'primary', 'faculty', 100.00, '2017-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.user_department_affiliations VALUES (11, 29, 6, 'primary', 'faculty', 100.00, '2021-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.user_department_affiliations VALUES (12, 30, 6, 'primary', 'faculty', 100.00, '2017-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.user_department_affiliations VALUES (13, 31, 7, 'primary', 'faculty', 100.00, '2017-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.user_department_affiliations VALUES (14, 32, 7, 'primary', 'faculty', 100.00, '2016-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:55', '2025-09-04 22:28:55');
INSERT INTO public.user_department_affiliations VALUES (15, 33, 7, 'primary', 'faculty', 100.00, '2018-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.user_department_affiliations VALUES (16, 34, 8, 'primary', 'faculty', 100.00, '2020-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.user_department_affiliations VALUES (17, 35, 8, 'primary', 'faculty', 100.00, '2018-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.user_department_affiliations VALUES (18, 36, 8, 'primary', 'faculty', 100.00, '2024-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.user_department_affiliations VALUES (19, 37, 8, 'primary', 'faculty', 100.00, '2017-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:56', '2025-09-04 22:28:56');
INSERT INTO public.user_department_affiliations VALUES (20, 38, 8, 'primary', 'faculty', 100.00, '2021-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.user_department_affiliations VALUES (21, 39, 9, 'primary', 'faculty', 100.00, '2024-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.user_department_affiliations VALUES (22, 40, 9, 'primary', 'faculty', 100.00, '2015-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.user_department_affiliations VALUES (23, 41, 9, 'primary', 'faculty', 100.00, '2019-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:57', '2025-09-04 22:28:57');
INSERT INTO public.user_department_affiliations VALUES (24, 42, 10, 'primary', 'faculty', 100.00, '2017-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.user_department_affiliations VALUES (25, 43, 10, 'primary', 'faculty', 100.00, '2023-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.user_department_affiliations VALUES (26, 44, 10, 'primary', 'faculty', 100.00, '2019-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.user_department_affiliations VALUES (27, 45, 11, 'primary', 'faculty', 100.00, '2017-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.user_department_affiliations VALUES (28, 46, 11, 'primary', 'faculty', 100.00, '2015-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:58', '2025-09-04 22:28:58');
INSERT INTO public.user_department_affiliations VALUES (29, 47, 11, 'primary', 'faculty', 100.00, '2024-09-04', NULL, true, 'Assistant Professor', NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.user_department_affiliations VALUES (30, 11, 9, 'cross_appointment', 'faculty', 20.00, '2025-03-04', NULL, true, 'Adjunct Faculty', NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');
INSERT INTO public.user_department_affiliations VALUES (31, 12, 4, 'secondary', 'faculty', 30.00, '2025-06-04', NULL, true, 'Visiting Faculty', NULL, '2025-09-04 22:28:59', '2025-09-04 22:28:59');


--
-- Data for Name: waitlists; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: what_if_scenarios; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: write_offs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Name: academic_calendars_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.academic_calendars_id_seq', 1, false);


--
-- Name: academic_period_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.academic_period_types_id_seq', 2, true);


--
-- Name: academic_plans_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.academic_plans_id_seq', 1, false);


--
-- Name: academic_programs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.academic_programs_id_seq', 13, true);


--
-- Name: academic_standing_changes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.academic_standing_changes_id_seq', 1, false);


--
-- Name: academic_terms_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.academic_terms_id_seq', 15, true);


--
-- Name: admission_applications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.admission_applications_id_seq', 57, true);


--
-- Name: admission_interviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.admission_interviews_id_seq', 1, false);


--
-- Name: admission_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.admission_settings_id_seq', 8, true);


--
-- Name: admission_waitlists_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.admission_waitlists_id_seq', 1, false);


--
-- Name: announcements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.announcements_id_seq', 1, false);


--
-- Name: answer_key_challenges_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.answer_key_challenges_id_seq', 1, false);


--
-- Name: applicants_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.applicants_id_seq', 4, true);


--
-- Name: application_checklist_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.application_checklist_items_id_seq', 307, true);


--
-- Name: application_communications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.application_communications_id_seq', 1, false);


--
-- Name: application_documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.application_documents_id_seq', 69, true);


--
-- Name: application_fees_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.application_fees_id_seq', 40, true);


--
-- Name: application_notes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.application_notes_id_seq', 1, false);


--
-- Name: application_reviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.application_reviews_id_seq', 10, true);


--
-- Name: application_statistics_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.application_statistics_id_seq', 1, false);


--
-- Name: application_status_histories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.application_status_histories_id_seq', 1, false);


--
-- Name: application_status_history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.application_status_history_id_seq', 1, false);


--
-- Name: application_templates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.application_templates_id_seq', 1, false);


--
-- Name: assignment_group_members_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.assignment_group_members_id_seq', 1, false);


--
-- Name: assignment_groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.assignment_groups_id_seq', 1, false);


--
-- Name: assignment_submissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.assignment_submissions_id_seq', 1, false);


--
-- Name: assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.assignments_id_seq', 1, false);


--
-- Name: attendance_alerts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_alerts_id_seq', 1, false);


--
-- Name: attendance_configurations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_configurations_id_seq', 1, true);


--
-- Name: attendance_excuses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_excuses_id_seq', 1, false);


--
-- Name: attendance_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_id_seq', 1, false);


--
-- Name: attendance_policies_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_policies_id_seq', 1, false);


--
-- Name: attendance_records_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_records_id_seq', 1, false);


--
-- Name: attendance_sessions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_sessions_id_seq', 1, false);


--
-- Name: attendance_statistics_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.attendance_statistics_id_seq', 1, false);


--
-- Name: billing_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.billing_items_id_seq', 1, false);


--
-- Name: buildings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.buildings_id_seq', 4, true);


--
-- Name: calendar_events_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.calendar_events_id_seq', 1, false);


--
-- Name: cities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cities_id_seq', 1, false);


--
-- Name: class_schedules_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.class_schedules_id_seq', 1, false);


--
-- Name: collection_accounts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.collection_accounts_id_seq', 1, false);


--
-- Name: colleges_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.colleges_id_seq', 9, true);


--
-- Name: content_access_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.content_access_logs_id_seq', 1, false);


--
-- Name: content_folders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.content_folders_id_seq', 1, false);


--
-- Name: content_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.content_items_id_seq', 1, false);


--
-- Name: countries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.countries_id_seq', 1, false);


--
-- Name: course_prerequisites_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.course_prerequisites_id_seq', 1, false);


--
-- Name: course_requirement_mappings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.course_requirement_mappings_id_seq', 8, true);


--
-- Name: course_sections_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.course_sections_id_seq', 21, true);


--
-- Name: course_sites_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.course_sites_id_seq', 20, true);


--
-- Name: courses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.courses_id_seq', 21, true);


--
-- Name: credit_configurations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.credit_configurations_id_seq', 1, true);


--
-- Name: credit_overload_permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.credit_overload_permissions_id_seq', 1, false);


--
-- Name: deans_list_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.deans_list_id_seq', 1, false);


--
-- Name: degree_audit_reports_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.degree_audit_reports_id_seq', 7, true);


--
-- Name: degree_requirements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.degree_requirements_id_seq', 32, true);


--
-- Name: degrees_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.degrees_id_seq', 13, true);


--
-- Name: departments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.departments_id_seq', 18, true);


--
-- Name: discussion_forums_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.discussion_forums_id_seq', 1, false);


--
-- Name: discussion_posts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.discussion_posts_id_seq', 1, false);


--
-- Name: divisions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.divisions_id_seq', 3, true);


--
-- Name: document_access_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.document_access_logs_id_seq', 2, true);


--
-- Name: document_processing_queue_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.document_processing_queue_id_seq', 4, true);


--
-- Name: document_relationships_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.document_relationships_id_seq', 42, true);


--
-- Name: document_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.document_requests_id_seq', 1, false);


--
-- Name: document_templates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.document_templates_id_seq', 1, false);


--
-- Name: document_versions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.document_versions_id_seq', 1, false);


--
-- Name: documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.documents_id_seq', 42, true);


--
-- Name: email_templates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.email_templates_id_seq', 1, false);


--
-- Name: enrollment_confirmations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.enrollment_confirmations_id_seq', 1, false);


--
-- Name: enrollment_histories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.enrollment_histories_id_seq', 1, false);


--
-- Name: enrollments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.enrollments_id_seq', 9, true);


--
-- Name: entrance_exam_registrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.entrance_exam_registrations_id_seq', 1, false);


--
-- Name: entrance_exam_results_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.entrance_exam_results_id_seq', 1, false);


--
-- Name: entrance_exams_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.entrance_exams_id_seq', 1, true);


--
-- Name: exam_answer_keys_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.exam_answer_keys_id_seq', 1, false);


--
-- Name: exam_centers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.exam_centers_id_seq', 1, true);


--
-- Name: exam_certificates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.exam_certificates_id_seq', 1, false);


--
-- Name: exam_proctoring_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.exam_proctoring_logs_id_seq', 1, false);


--
-- Name: exam_question_papers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.exam_question_papers_id_seq', 1, false);


--
-- Name: exam_questions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.exam_questions_id_seq', 1, false);


--
-- Name: exam_response_details_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.exam_response_details_id_seq', 1, false);


--
-- Name: exam_responses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.exam_responses_id_seq', 1, false);


--
-- Name: exam_seat_allocations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.exam_seat_allocations_id_seq', 1, false);


--
-- Name: exam_sessions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.exam_sessions_id_seq', 1, true);


--
-- Name: faculty_availability_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.faculty_availability_id_seq', 1, false);


--
-- Name: faculty_course_assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.faculty_course_assignments_id_seq', 1, false);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: fee_structures_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.fee_structures_id_seq', 13, true);


--
-- Name: final_grades_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.final_grades_id_seq', 1, false);


--
-- Name: financial_aid_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.financial_aid_id_seq', 1, false);


--
-- Name: financial_holds_history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.financial_holds_history_id_seq', 1, false);


--
-- Name: financial_transactions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.financial_transactions_id_seq', 1, false);


--
-- Name: grade_audit_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grade_audit_log_id_seq', 1, false);


--
-- Name: grade_change_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grade_change_requests_id_seq', 1, false);


--
-- Name: grade_components_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grade_components_id_seq', 1, false);


--
-- Name: grade_deadlines_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grade_deadlines_id_seq', 5, true);


--
-- Name: grade_scales_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grade_scales_id_seq', 5, true);


--
-- Name: grade_submissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grade_submissions_id_seq', 1, false);


--
-- Name: gradebook_entries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.gradebook_entries_id_seq', 1, false);


--
-- Name: gradebook_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.gradebook_items_id_seq', 1, false);


--
-- Name: grades_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grades_id_seq', 1, false);


--
-- Name: grading_configurations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grading_configurations_id_seq', 1, true);


--
-- Name: graduation_applications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.graduation_applications_id_seq', 1, false);


--
-- Name: import_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.import_logs_id_seq', 1, false);


--
-- Name: institution_config_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.institution_config_id_seq', 1, true);


--
-- Name: invoices_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.invoices_id_seq', 1, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: lms_announcements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.lms_announcements_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 100, true);


--
-- Name: office_appointments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.office_appointments_id_seq', 1, false);


--
-- Name: office_hours_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.office_hours_id_seq', 1, false);


--
-- Name: organizational_permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.organizational_permissions_id_seq', 12, true);


--
-- Name: override_approval_routes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.override_approval_routes_id_seq', 5, true);


--
-- Name: payment_allocations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.payment_allocations_id_seq', 1, false);


--
-- Name: payment_gateway_transactions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.payment_gateway_transactions_id_seq', 1, false);


--
-- Name: payment_gateways_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.payment_gateways_id_seq', 4, true);


--
-- Name: payment_plan_schedules_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.payment_plan_schedules_id_seq', 1, false);


--
-- Name: payment_plans_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.payment_plans_id_seq', 1, false);


--
-- Name: payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.payments_id_seq', 1, false);


--
-- Name: permission_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.permission_role_id_seq', 303, true);


--
-- Name: permission_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.permission_user_id_seq', 1, false);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.permissions_id_seq', 146, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: plan_courses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.plan_courses_id_seq', 1, false);


--
-- Name: plan_terms_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.plan_terms_id_seq', 1, false);


--
-- Name: prerequisite_overrides_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.prerequisite_overrides_id_seq', 1, false);


--
-- Name: prerequisite_waivers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.prerequisite_waivers_id_seq', 1, false);


--
-- Name: program_courses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.program_courses_id_seq', 1, false);


--
-- Name: program_prerequisites_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.program_prerequisites_id_seq', 1, false);


--
-- Name: program_requirements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.program_requirements_id_seq', 32, true);


--
-- Name: program_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.program_types_id_seq', 6, true);


--
-- Name: quiz_attempts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.quiz_attempts_id_seq', 1, false);


--
-- Name: quiz_questions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.quiz_questions_id_seq', 1, false);


--
-- Name: quizzes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.quizzes_id_seq', 1, false);


--
-- Name: recommendation_letters_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.recommendation_letters_id_seq', 1, false);


--
-- Name: refunds_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.refunds_id_seq', 1, false);


--
-- Name: registration_carts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.registration_carts_id_seq', 2, true);


--
-- Name: registration_configurations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.registration_configurations_id_seq', 1, true);


--
-- Name: registration_holds_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.registration_holds_id_seq', 1, false);


--
-- Name: registration_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.registration_logs_id_seq', 1, false);


--
-- Name: registration_override_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.registration_override_requests_id_seq', 1, false);


--
-- Name: registration_overrides_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.registration_overrides_id_seq', 1, false);


--
-- Name: registration_periods_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.registration_periods_id_seq', 3, true);


--
-- Name: registrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.registrations_id_seq', 4, true);


--
-- Name: requirement_categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.requirement_categories_id_seq', 18, true);


--
-- Name: requirement_substitutions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.requirement_substitutions_id_seq', 1, false);


--
-- Name: role_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.role_user_id_seq', 185, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.roles_id_seq', 21, true);


--
-- Name: room_availability_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.room_availability_id_seq', 1, false);


--
-- Name: room_bookings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.room_bookings_id_seq', 1, false);


--
-- Name: rooms_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.rooms_id_seq', 40, true);


--
-- Name: rubric_criteria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.rubric_criteria_id_seq', 1, false);


--
-- Name: rubrics_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.rubrics_id_seq', 1, false);


--
-- Name: schedule_changes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.schedule_changes_id_seq', 1, false);


--
-- Name: schedule_conflicts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.schedule_conflicts_id_seq', 1, false);


--
-- Name: schools_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.schools_id_seq', 4, true);


--
-- Name: scope_audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.scope_audit_logs_id_seq', 1, false);


--
-- Name: section_attendance_policies_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.section_attendance_policies_id_seq', 1, false);


--
-- Name: section_schedules_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.section_schedules_id_seq', 1, false);


--
-- Name: special_registration_flags_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.special_registration_flags_id_seq', 1, false);


--
-- Name: sponsor_authorizations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.sponsor_authorizations_id_seq', 1, false);


--
-- Name: states_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.states_id_seq', 1, false);


--
-- Name: student_accounts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.student_accounts_id_seq', 102, true);


--
-- Name: student_conversions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.student_conversions_id_seq', 1, false);


--
-- Name: student_course_applications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.student_course_applications_id_seq', 1, false);


--
-- Name: student_degree_progress_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.student_degree_progress_id_seq', 33, true);


--
-- Name: student_holds_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.student_holds_id_seq', 1, false);


--
-- Name: student_honors_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.student_honors_id_seq', 1, false);


--
-- Name: student_progress_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.student_progress_id_seq', 1, false);


--
-- Name: student_status_changes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.student_status_changes_id_seq', 1, false);


--
-- Name: students_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.students_id_seq', 105, true);


--
-- Name: system_modules_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.system_modules_id_seq', 10, true);


--
-- Name: system_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.system_settings_id_seq', 13, true);


--
-- Name: teaching_assistants_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.teaching_assistants_id_seq', 1, false);


--
-- Name: teaching_loads_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.teaching_loads_id_seq', 1, false);


--
-- Name: third_party_sponsors_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.third_party_sponsors_id_seq', 1, false);


--
-- Name: time_slots_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.time_slots_id_seq', 9, true);


--
-- Name: timetable_templates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.timetable_templates_id_seq', 1, true);


--
-- Name: transcript_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.transcript_logs_id_seq', 6, true);


--
-- Name: transcript_payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.transcript_payments_id_seq', 1, false);


--
-- Name: transcript_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.transcript_requests_id_seq', 5, true);


--
-- Name: transcript_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.transcript_verifications_id_seq', 1, false);


--
-- Name: transfer_credits_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.transfer_credits_id_seq', 1, false);


--
-- Name: user_activity_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_activity_logs_id_seq', 268, true);


--
-- Name: user_department_affiliations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_department_affiliations_id_seq', 31, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 196, true);


--
-- Name: waitlists_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.waitlists_id_seq', 1, false);


--
-- Name: what_if_scenarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.what_if_scenarios_id_seq', 1, false);


--
-- Name: write_offs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.write_offs_id_seq', 1, false);


--
-- PostgreSQL database dump complete
--

\unrestrict 3O9bI7wZdjybjFgT0X3TNFv1cW7BXdnEQaGnovchm2xhgBXeLhTdJ3AIl0Ue1ek

