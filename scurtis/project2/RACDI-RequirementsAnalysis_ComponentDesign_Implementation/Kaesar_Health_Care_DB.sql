CREATE SCHEMA KAESAR_HEALTH_CARE;
CREATE TABLE PROVIDER
(
	ProviderID			SMALLINT				NOT NULL AUTO_INCREMENT,
    Email		    	VARCHAR(255)			NOT NULL UNIQUE,
    Pass		    	CHAR(30)				NOT NULL,
    ProviderLast		CHAR(30)				NOT NULL,
    ProviderFirst		CHAR(30)				NOT NULL,
    DoB					DATE					NOT NULL,
    Country				CHAR(70)				NOT NULL,
    State				CHAR(2)					NOT NULL,
    City				CHAR(255)				NOT NULL,
    Zip					CHAR(10)				NOT NULL,
    Address				CHAR(255)				NULL,
    Apt					CHAR(6)					NULL,
    CONSTRAINT			PROVIDER_PK				PRIMARY KEY (ProviderID)
);
CREATE TABLE PATIENT
(
	PatientID			MEDIUMINT				NOT NULL AUTO_INCREMENT,
    Email		    	VARCHAR(255)			NOT NULL UNIQUE,
    Pass		    	CHAR(30)				NOT NULL,
    PatientLast			CHAR(30)				NOT NULL,
    PatientFirst		CHAR(30)				NOT NULL,
    DoB					DATE					NOT NULL,
    Country				CHAR(70)				NOT NULL,
    State				CHAR(2)					NOT NULL,
    City				CHAR(255)				NOT NULL,
    Zip					CHAR(10)				NOT NULL,
    Address				CHAR(255)				NULL,
    Apt					CHAR(6)					NULL,
    CONSTRAINT			PATIENT_PK				PRIMARY KEY (PatientID)
);
CREATE TABLE APPOINTMENT
(
	ProviderID			SMALLINT				NOT NULL,
    PatientID		    MEDIUMINT				NOT NULL,
    ApptDate		    DATE					NOT NULL,
    ApptTime			TIME					NOT NULL,
    Department			CHAR(30)				NOT NULL,
    Room				SMALLINT				NOT NULL,
    CONSTRAINT			APP_PK					PRIMARY KEY (ProviderID, PatientID),
    CONSTRAINT			APP_PROV_FK				FOREIGN KEY (ProviderID)
						REFERENCES				PROVIDER (ProviderID)
                        ON UPDATE NO ACTION
                        ON DELETE CASCADE,
    CONSTRAINT			APP_PAT_FK				FOREIGN KEY (PatientID)
						REFERENCES				PATIENT (PatientID)
                        ON UPDATE NO ACTION
                        ON DELETE CASCADE
);
/* use a join statement for additional info */
CREATE TABLE ARRIVAL
(
	PatientID			MEDIUMINT				NOT NULL,
    CallIn				BOOL					NOT NULL DEFAULT '0',
    CheckIn				BOOL 					NOT NULL DEFAULT '0',
    ArrDate				DATE					NOT NULL,
    ArrTime				TIME					NOT NULL,
    CONSTRAINT			ARRIVAL_PK				PRIMARY KEY (PatientID),
	CONSTRAINT			ARR_PAT_FK				FOREIGN KEY (PatientID)
						REFERENCES				PATIENT (PatientID)
);
/* N = 0, Y = 1 */

