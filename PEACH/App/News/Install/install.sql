# -----------------------------------------------------------------------
# news 
# -----------------------------------------------------------------------
drop table if exists news;

CREATE TABLE news(
 
	id INTEGER (11) NOT NULL auto_increment,	                              
 
	subject VARCHAR(255) default '' NOT NULL ,	                              
 
	summary TEXT ,	                              
 
	body TEXT ,	                              
 
	image_id INTEGER default 0 NOT NULL ,	                              
 
	hits INTEGER default 0 NOT NULL ,	                              
 
	approved SMALLINT default 0 NOT NULL ,	                              
 
	active SMALLINT default 1 NOT NULL ,	                              
 
	comments SMALLINT default 0 NOT NULL ,	                              
 
	anonymous SMALLINT default 0 NOT NULL ,	                              
 
	userCreated VARCHAR(20) default '' NOT NULL ,	                              
 
	userUpdated VARCHAR(20) default '' NOT NULL ,	                              
 
	dateCreated DATETIME default '0000-00-00 00:00:00' NOT NULL ,	                              
 
	dateUpdated DATETIME default '0000-00-00 00:00:00' NOT NULL ,	                              
 
	poston DATETIME default '0000-00-00 00:00:00' NOT NULL ,	                              
 
	expiration DATETIME default '0000-00-00 00:00:00',	                              
 
	cat_id INTEGER default 0,	                              
    
    PRIMARY KEY(id),
	INDEX (image_id),
    FOREIGN KEY (image_id) REFERENCES images (id),
	INDEX (cat_id),
    FOREIGN KEY (cat_id) REFERENCES categories (id));
