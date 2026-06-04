ALTER TABLE sm_terms ADD next_term_begins DATE NOT NULL AFTER term_name;
ALTER TABLE sm_terms ADD school_open_days INT NOT NULL AFTER next_term_begins;