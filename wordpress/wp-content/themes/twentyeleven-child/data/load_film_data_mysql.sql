-- mysql -uXXXXXXXXXXXXX -pXXXXXXXXXX -DXXXXXXXXXXXX -e "source load_film_data_mysql.sql"

TRUNCATE TABLE wp_movie_q;
TRUNCATE TABLE wp_director_link_movie;
TRUNCATE TABLE wp_director_link;
TRUNCATE TABLE wp_actor_link_movie;
TRUNCATE TABLE wp_actor_link;
TRUNCATE TABLE wp_actors;
TRUNCATE TABLE wp_movieview;

LOAD DATA LOCAL INFILE 'movie.csv' 
INTO TABLE wp_movieview  
CHARACTER SET latin1 
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"' 
LINES TERMINATED BY '\n' 
IGNORE 1 LINES (id, file_id, c00, c01, c02, c03, c04, c05, c06, c07, c08, c09, c10, c11, c12, c13, c14, c15, c16, c17, c18, c19, c20, c21, c22, c23);

LOAD DATA LOCAL INFILE 'actors.csv' 
INTO TABLE wp_actors  
CHARACTER SET latin1 
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"' 
LINES TERMINATED BY '\n' 
IGNORE 1 LINES (id, actor, thumb);

LOAD DATA LOCAL INFILE 'actorlink.csv' 
INTO TABLE wp_actor_link  
CHARACTER SET latin1 
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"' 
LINES TERMINATED BY '\n' 
IGNORE 1 LINES (actor_id, media_id, media_type, role, order_num);

LOAD DATA LOCAL INFILE 'directorlink.csv' 
INTO TABLE wp_director_link  
CHARACTER SET latin1 
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"' 
LINES TERMINATED BY '\n' 
IGNORE 1 LINES (actor_id, media_id, media_type);

INSERT INTO wp_actor_link_movie (actor_id, movie_id, role, order_num)
SELECT actor_id, media_id, role, order_num
  FROM wp_actor_link
 WHERE media_type = 'movie';

INSERT INTO wp_director_link_movie (actor_id, movie_id)
SELECT actor_id, media_id
  FROM wp_director_link
 WHERE media_type = 'movie';

INSERT INTO wp_movie_q (actor_id, movie_id, actor, rownum)
SELECT actor_id, movie_id, actor, rownum
 FROM wp_v_movie_q;


SET SQL_SAFE_UPDATES=0;
UPDATE wp_movieview mv 
  JOIN wp_v_movie_summary v ON mv.id = v.id 
   SET mv.actors = v.actors, 
       mv.directors = v.directors, 
       mv.sort_title = v.sort_title, 
       mv.duration = v.duration;

-- update synopsis to correct language for Orphans 
UPDATE temperec_crock.wp_movieview
   SET c01 = c02
  WHERE c09 = 'tt0119842';


