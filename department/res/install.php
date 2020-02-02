
<?php


	/****************************************
	*---------------------------------------*
	*|Create admin	details TABLE      	   |*
	*---------------------------------------*
	****************************************/

	mysqli_query($con, "CREATE TABLE IF NOT EXISTS admin(
			id int(11) AUTO_INCREMENT PRIMARY KEY,
		    name VARCHAR(255) NOT NULL,
		    username VARCHAR(255) NOT NULL,
		    password VARCHAR(255) NOT NULL,
		    email VARCHAR(255) NOT NULL,
			current_sesh VARCHAR(255),
			current_semester VARCHAR(255),
			`image` VARCHAR(255)
		);"
	);

	/****************************************
	*---------------------------------------*
	*|Create results TABLE            	   |*
	*---------------------------------------*
	****************************************/

	mysqli_query($con, "CREATE TABLE IF NOT EXISTS results (
			id int(11) AUTO_INCREMENT PRIMARY KEY,
		    course VARCHAR(255),
		    code VARCHAR(255),
		    units VARCHAR(255),
		    score VARCHAR(255),
		    grade VARCHAR(255),
		    name VARCHAR(255),
		    matric VARCHAR(255),
		    sesh VARCHAR(255),
		    semester VARCHAR(255),
		    department VARCHAR(255),
		    sesh_submitted VARCHAR(255),
			ca VARCHAR(255),
		    exam VARCHAR(255),
			view_stat VARCHAR(255),
			yoe VARCHAR(255)
		);"
	);

	/****************************************
	*---------------------------------------*
	*|Create departments TABLE         	   |*
	*---------------------------------------*
	****************************************/

	mysqli_query($con, "CREATE TABLE IF NOT EXISTS departments(
			id int(11) AUTO_INCREMENT PRIMARY KEY,
		    dept VARCHAR(255)
		);"
	);

	/****************************************
	*---------------------------------------*
	*|Create lecturers TABLE         	   |*
	*---------------------------------------*
	****************************************/

	mysqli_query($con, "CREATE TABLE IF NOT EXISTS lecturers(
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
		    name VARCHAR(255),
		    username VARCHAR(255),
		    password VARCHAR(255),
		    email VARCHAR(255),
		    status VARCHAR(255),
			`image` VARCHAR(255)
		);"
	);

	/**
	 * Courses table
	 */
	mysqli_query($con, "
		CREATE TABLE IF NOT EXISTS courses(
			id int(11) auto_increment primary key,
			code varchar(255),
			title varchar(255),
			slowed varchar(255) not null default '0',
			department varchar(255),
			semester varchar(255),
			unit varchar(255),
			assigned_to varchar(255) not null default 'NOT SET',
			sesh varchar(255),
			state varchar(255) not null default 'CORE',
			sesh_assigned varchar(255),
			sesh_added varchar(255)
		);
	");
	/**
	 * Courses Registered Table
	 */
	mysqli_query($con, "
		CREATE TABLE IF NOT EXISTS course_registered(
			id int(11) auto_increment primary key,
			yoe varchar(255),
			code varchar(255),
			title varchar(255),
			student_id varchar(255),
			unit varchar(255),
			sesh varchar(255),
			semester varchar(255),
			status varchar(255) not null default 'NOT SUBMITTED',
			matric varchar(255),
			slowed varchar(255) not null default '0',
			department varchar(255)
		);
	");

	/**
	 * Grades Table
	 */
	mysqli_query($con,"
		CREATE TABLE IF NOT EXISTS grades(
			id int(11) auto_increment primary key,
			grade varchar(255),
			gradepoints varchar(255),
			sesh_updated varchar(255),
			minimum_score varchar(255)
		);
	");

	/**
	 * Levels Table
	 */
	mysqli_query($con, "
		CREATE TABLE IF NOT EXISTS levels(
			id int(11) auto_increment primary key,
			level varchar(255)
		);
	");

	/**
	 * Notifications Table
	 */
	mysqli_query($con, "
		CREATE TABLE IF NOT EXISTS notifications(
			id int(11) auto_increment primary key,
			notification varchar(255),
			not_for varchar(255),
			view_stat varchar(255),
			admin_review varchar(255),
			user_id varchar(255)
		);
	");

	/**
	 *Students Table 
	 */
	mysqli_query($con, "
		CREATE TABLE IF NOT EXISTS students(
			id int(11) auto_increment primary key,
			name varchar(255),
			username varchar(255),
			password varchar(255),
			email varchar(255),
			matric varchar(255),
			department varchar(255),
			entry_year varchar(255),
			yoe varchar(255),
			`image` VARCHAR(255)
		);
	");

?>