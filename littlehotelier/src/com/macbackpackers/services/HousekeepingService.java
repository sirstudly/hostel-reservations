package com.macbackpackers.services;

import java.sql.SQLException;

import com.macbackpackers.beans.Job;
import com.macbackpackers.dao.WordPressDAO;

public class HousekeepingService {
    
   // TODO: set as properties 
    static final String username = "root";
    static final String password = "system??";

    /**
     * Checks for any housekeeping jobs that need to be run ('submitted') and 
     * processes them.
     * @throws SQLException 
     */
    public void processJob( Job job ) throws SQLException {
        // find submitted jobs
        WordPressDAO dao = new WordPressDAO("jdbc:mysql://", "localhost", "3306", "wordpress701");
        dao.connect(username, password);

        dao.updateJobStatus( job.getId(), "processing" );
        
        
    }
}