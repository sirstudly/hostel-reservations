package com.macbackpackers.services;

import java.sql.SQLException;

import com.macbackpackers.beans.Job;
import com.macbackpackers.dao.WordPressDAO;

public class ProcessorService {
    
    static final String username = "root";
    static final String password = "system??";

    private HousekeepingService housekeeping = new HousekeepingService();
    
    /**
     * Checks for any housekeeping jobs that need to be run ('submitted') and 
     * processes them.
     * @throws SQLException on error
     */
    public void processJobs() throws SQLException {
        // find submitted jobs
        WordPressDAO dao = new WordPressDAO("jdbc:mysql://", "localhost", "3306", "wordpress701");
        dao.connect(username, password);

        Job job = dao.getNextJobToProcess();
        
        if( "bedsheets".equals( job.getName() )) {
            housekeeping.processJob( job );
        }
    }
}