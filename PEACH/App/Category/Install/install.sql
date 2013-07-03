# -----------------------------------------------------------------------
# categories 
# -----------------------------------------------------------------------
drop table if exists categories;

CREATE TABLE categories(
 
	id INTEGER (11) NOT NULL auto_increment,	                              
 
	title VARCHAR(60) ,	                              
 
	description TEXT ,	                              
 
	image_id INTEGER ,	                              
 
	parent_id INTEGER default 0,	                              
 
	children VARCHAR(255) ,	                              
    
    PRIMARY KEY(id),
	INDEX (image_id),
    FOREIGN KEY (image_id) REFERENCES images (id),
	INDEX (parent_id),
    FOREIGN KEY (parent_id) REFERENCES categories (id));
