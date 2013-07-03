# -----------------------------------------------------------------------
# images 
# -----------------------------------------------------------------------
drop table if exists images;

CREATE TABLE images(
 
	label VARCHAR(255) default '' NOT NULL ,	                              
 
	file VARCHAR(255) default '' NOT NULL ,	                              
 
	name VARCHAR(255) default '' NOT NULL ,	                              
 
	extension VARCHAR(5) default '' NOT NULL ,	                              
 
	width VARCHAR(20) default '0' NOT NULL ,	                              
 
	height VARCHAR(20) default '0' NOT NULL ,	                              
 
	size INTEGER default 0 NOT NULL ,	                              
 
	mime VARCHAR(20) default '0' NOT NULL ,	                              
 
	absolutepath VARCHAR(255) default '0' NOT NULL ,	                              
 
	webpath VARCHAR(255) default '0' NOT NULL ,	                              
 
	fqdn VARCHAR(255) default '0' NOT NULL ,	                              
 
	flavour VARCHAR(255) ,	                              
 
	parent_id INTEGER ,	                              
 
	children VARCHAR(255) ,	                              
 
	owner VARCHAR(20) default '' ,	                              
 
	editor VARCHAR(20) default '' ,	                              
 
	ip VARCHAR(255) ,	                              
 
	created INTEGER default 0 NOT NULL ,	                              
 
	updated INTEGER default 0 NOT NULL ,	                              
 
	hidden INTEGER default 1 NOT NULL ,	                              
 
	approved INTEGER default 0 NOT NULL ,	                              
 
	id INTEGER default 0 NOT NULL ,	                              
 
	cat_id INTEGER default 0 NOT NULL ,	                              
	INDEX (cat_id),
    FOREIGN KEY (cat_id) REFERENCES categories (id),
	INDEX (parent_id),
    FOREIGN KEY (parent_id) REFERENCES images (id));
