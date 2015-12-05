<?php
    
  function my_scripts_method() {
	wp_enqueue_script(
		'idTabs',
		get_stylesheet_directory_uri() . '/js/jquery.idTabs.min.js',
		array()
	);
  }

  add_action( 'wp_enqueue_scripts', 'my_scripts_method' );
  add_action( 'after_setup_theme', 'setup_database' );
 
  function setup_database() {

    global $wpdb;
    $charset_collate = '';
    if (false === empty($wpdb->charset)) {
      $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
    }

    if ( false == does_table_exist('movieview') ) { 
      $simple_sql = "CREATE TABLE ".$wpdb->prefix ."movieview (
                    id bigint(20) unsigned NOT NULL,
                    c00 varchar(255),
                    c01 text,
                    c02 text,
                    c03 text,
                    c04 varchar(255),
                    c05 decimal(2,1),
                    c06 varchar(255),
                    c07 varchar(255),
                    c08 text,
                    c09 varchar(255),
                    c10 varchar(255),
                    c11 varchar(255),
                    c12 varchar(255),
                    c13 varchar(255),
                    c14 varchar(255),
                    c15 varchar(255),
                    c16 varchar(255),
                    c17 varchar(255),
                    c18 varchar(255),
                    c19 varchar(255),
                    c20 text,
                    c21 varchar(255),
                    c22 text,
                    c23 varchar(255),
                    file_id bigint(20) unsigned,
                    actors text,
                    directors text,
                    sort_title varchar(255),
                    duration varchar(255),
                    PRIMARY KEY (id),
                    INDEX idx_mv_c00 (c00)
                ) $charset_collate;";

      execute_simple_sql($simple_sql);
      error_log( "table movieview created" );
    }

    if ( false == does_table_exist('actors') ) { 
      $simple_sql = "CREATE TABLE ".$wpdb->prefix ."actors (
                      id bigint(20) unsigned NOT NULL,
                      actor varchar(255),
                      thumb varchar(255),
                      PRIMARY KEY (id)
                    ) $charset_collate;";

        execute_simple_sql($simple_sql);
        error_log( "table actors created" );
    }

    if ( false == does_table_exist('director_link_movie') ) { 
      $simple_sql = "CREATE TABLE ".$wpdb->prefix ."director_link_movie (
                    id bigint(20) unsigned NOT NULL auto_increment,
                    actor_id bigint(20) unsigned NOT NULL,
                    movie_id bigint(20) unsigned NOT NULL,
                    PRIMARY KEY (id),
                    FOREIGN KEY (actor_id) REFERENCES ".$wpdb->prefix ."actors(id),
                    FOREIGN KEY (movie_id) REFERENCES ".$wpdb->prefix ."movieview(id),
                    INDEX idx_mov_dir_actor_id (actor_id),
                    INDEX idx_mov_dir_movie_id (movie_id),
                    INDEX idx_mov_dir_id (movie_id, actor_id)
                ) $charset_collate;";

        execute_simple_sql($simple_sql);
        error_log( "table director_link_movie created" );
    }

    if ( false == does_table_exist('actor_link_movie') ) { 
        $simple_sql = "CREATE TABLE ".$wpdb->prefix ."actor_link_movie (
                    id bigint(20) unsigned NOT NULL auto_increment,
                    actor_id bigint(20) unsigned NOT NULL,
                    movie_id bigint(20) unsigned NOT NULL,
                    role varchar(255),
                    order_num varchar(255),
                    PRIMARY KEY (id),
                    FOREIGN KEY (actor_id) REFERENCES ".$wpdb->prefix ."actors(id),
                    FOREIGN KEY (movie_id) REFERENCES ".$wpdb->prefix ."movieview(id),
                    INDEX idx_mov_act_actor_id (actor_id),
                    INDEX idx_mov_act_movie_id (movie_id),
                    INDEX idx_mov_act_id (movie_id, actor_id)
                ) $charset_collate;";

        execute_simple_sql($simple_sql);
        error_log( "table actor_link_movie created" );
    }

    if ( false == does_table_exist('v_movie_actor_link') ) { 
        $simple_sql = 
         "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_movie_actor_link AS
          SELECT m.id AS movie_id, a.id AS actor_id, a.actor 
            FROM wp_actor_link_movie AS alm
            JOIN wp_actors AS a ON alm.actor_id = a.id 
            JOIN wp_movieview AS m ON alm.movie_id = m.id 
            ORDER BY m.c00, a.id";

        execute_simple_sql($simple_sql);
        error_log( "view v_movie_actor_link created" );
    }

    if ( false == does_table_exist('v_movie_q') ) { 
        $simple_sql = 
              "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_movie_q AS
               SELECT Q.movie_id, Q.actor_id, Q.actor, Count(1) AS rownum
                 FROM wp_v_movie_actor_link AS Q, 
                      wp_v_movie_actor_link AS R
                WHERE Q.movie_id = R.movie_id 
                  And Q.actor_id >= R.actor_id
                GROUP BY Q.movie_id, Q.actor_id, Q.actor";

        execute_simple_sql($simple_sql);
        error_log( "view v_movie_q created" );
    }

    if ( false == does_table_exist('movie_q') ) { 
        $simple_sql = "CREATE TABLE ".$wpdb->prefix ."movie_q (
                    id bigint(20) unsigned NOT NULL auto_increment,
                    actor_id bigint(20) unsigned NOT NULL,
                    movie_id bigint(20) unsigned NOT NULL,
                    actor varchar(255),
                    rownum int(255),
                    PRIMARY KEY (id),
                    FOREIGN KEY (actor_id) REFERENCES ".$wpdb->prefix ."actors(id),
                    FOREIGN KEY (movie_id) REFERENCES ".$wpdb->prefix ."movieview(id),
                    INDEX idx_movieq_mov_rownum (movie_id, rownum)
                ) $charset_collate;";

        execute_simple_sql($simple_sql);
        error_log( "table movie_q created" );
    }

    if ( false == does_table_exist('v_movie_join_actors_flatten') ) { 
        $simple_sql = 
           "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_movie_join_actors_flatten AS
            SELECT mv.id AS movie_id, mv.c00, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=1) AS actor1, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=2) AS actor2, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=3) AS actor3, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=4) AS actor4, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=5) AS actor5, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=6) AS actor6, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=7) AS actor7, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=8) AS actor8, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=9) AS actor9, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=10) AS actor10, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=11) AS actor11, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=12) AS actor12, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=13) AS actor13, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=14) AS actor14, 
		         (SELECT actor FROM wp_movie_q WHERE mv.id = movie_id and rownum=15) AS actor15 
             FROM ".$wpdb->prefix."movieview AS mv";

        execute_simple_sql($simple_sql);
        error_log( "view v_movie_join_actors_flatten created" );
    }

    if ( false == does_table_exist('v_coalesce_actors') ) { 
        $simple_sql = 
            "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_coalesce_actors AS
             SELECT movie_id, CONCAT( actor1, 
                 IF( actor2 is null, '', CONCAT( ', ', actor2 )),
                 IF( actor3 is null, '', CONCAT( ', ', actor3 )),
                 IF( actor4 is null, '', CONCAT( ', ', actor4 )),
                 IF( actor5 is null, '', CONCAT( ', ', actor5 )),
                 IF( actor6 is null, '', CONCAT( ', ', actor6 )),
                 IF( actor7 is null, '', CONCAT( ', ', actor7 )),
                 IF( actor8 is null, '', CONCAT( ', ', actor8 )),
                 IF( actor9 is null, '', CONCAT( ', ', actor9 )),
                 IF( actor10 is null, '', CONCAT( ', ', actor10 )),
                 IF( actor11 is null, '', CONCAT( ', ', actor11 )),
                 IF( actor12 is null, '', CONCAT( ', ', actor12 )),
                 IF( actor13 is null, '', CONCAT( ', ', actor13 )),
                 IF( actor14 is null, '', CONCAT( ', ', actor14 )),
                 IF( actor15 is null, '', CONCAT( ', ', actor15 ))) AS actors
            FROM ".$wpdb->prefix."v_movie_join_actors_flatten";

        execute_simple_sql($simple_sql);
        error_log( "view v_coalesce_actors created" );
    }

    if ( false == does_table_exist('v_movie_director_link') ) { 
        $simple_sql = 
         "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_movie_director_link AS
            SELECT m.id AS movie_id, a.id AS actor_id, a.actor 
              FROM ".$wpdb->prefix."director_link_movie AS dlm 
              JOIN ".$wpdb->prefix."actors AS a ON dlm.actor_id = a.id 
              JOIN ".$wpdb->prefix."movieview AS m ON dlm.movie_id = m.id
             ORDER BY m.c00, a.id";

        execute_simple_sql($simple_sql);
        error_log( "view v_movie_director_link created" );
    }

    if ( false == does_table_exist('v_movie_d') ) { 
        $simple_sql = 
              "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_movie_d AS
               SELECT Q.movie_id, Q.actor_id AS actor_id, Q.actor AS director, Count(1) AS rownum
	             FROM ".$wpdb->prefix."v_movie_director_link AS Q, 
		              ".$wpdb->prefix."v_movie_director_link AS R
	            WHERE Q.movie_id = R.movie_id 
	              AND Q.actor_id >= R.actor_id
	            GROUP BY Q.movie_id, Q.actor_id, Q.actor";

        execute_simple_sql($simple_sql);
        error_log( "view v_movie_d created" );
    }

    if ( false == does_table_exist('v_movie_summary') ) { 
        $simple_sql = 
              "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_movie_summary AS
               SELECT m.id, m.c00 AS title,
                      CONCAT( d1.director, IF( d2.director IS NULL, '', CONCAT(', ', d2.director ))) AS directors,
                      a.actors,
                      CASE WHEN c00 LIKE 'The %' THEN SUBSTRING( c00, 5 )
                           WHEN c00 LIKE 'A %' THEN SUBSTRING( c00, 3 )
                           WHEN c00 LIKE 'An %' THEN SUBSTRING( c00, 4 )
                           WHEN c00 LIKE '[%' THEN SUBSTRING( c00, 2 )
                           ELSE c00
                      END AS sort_title,
                      IF( c11 REGEXP '^[0-9]+$', CONCAT( c11, ' min' ), c11 ) AS duration
                 FROM ".$wpdb->prefix."movieview m
                 LEFT OUTER JOIN ".$wpdb->prefix."v_movie_d d1 ON m.id = d1.movie_id AND d1.rownum = 1
                 LEFT OUTER JOIN ".$wpdb->prefix."v_movie_d d2 ON m.id = d2.movie_id AND d2.rownum = 2
                 LEFT OUTER JOIN ".$wpdb->prefix."v_coalesce_actors a ON a.movie_id = m.id";

        execute_simple_sql($simple_sql);
        error_log( "view v_movie_summary created" );
    }

  }

    /**
     * Check if table exists.
     * $tablename : name of table to check (with or without wp prefix)
     * Returns true or false.
     */
    function does_table_exist( $tablename ) {
        global $wpdb;
        if (strpos($tablename, $wpdb->prefix) === false) {
            $tablename = $wpdb->prefix . $tablename;   
        }

        $res = $wpdb->get_results($wpdb->prepare(
                "SELECT COUNT(*) AS count
                   FROM information_schema.tables
                  WHERE table_schema = '". DB_NAME ."'
                    AND table_name = %s", $tablename));
        return $res[0]->count > 0;
    }

    /**
     * Executes a single SQL statement.
     * $simple_sql : sql statement to execute
     */
    function execute_simple_sql($simple_sql) {
        global $wpdb;
        if (false === $wpdb->query($simple_sql)) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
        }
    }

?>
