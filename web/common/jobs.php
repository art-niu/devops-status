<?php 
class dynamicsJob { 
    public $jobId; 
    public $catagory; 
    public $task;
    public $cmd;
    public $remoteServer;
    public $runAs;
    public $credential;
    public $userName;
    public $startAt;
    public $finishAt;
    public $elapsed;
    public $status;
    public $logUrl;
    public $logFile;
    public $consoleLog;
    
    //Create JSON file
    function createUpdateJob() { 

        require ("../config/dbconf.php");
        $collection = "tcsit.jobs";
        
        $bulk = new MongoDB\Driver\BulkWrite();

        $bulk->update([
            'jobid' => $this->jobId
        ], [
            '$set' => [
                'jobid' => $this->jobId,
                'catagory' => $this->catagory,
                'task' => $this->task,
                'cmd' => $this->cmd,
                'remoteserver' => $this->remoteServer,
                'runas' => $this->runAs,
                'credential' => $this->credential,
                'username' => $this->userName,
                'startat' => $this->startAt,
                'finishat' => $this->finishAt,
                'elapsed' => $this->elapsed,
                'status' => $this->status,
                'logfile' => $this->logUrl,
                'logurl' => $this->logFile,
                'consolelog' => $this->consoleLog
            ]
        ], [
            'multi' => true,
            'upsert' => true
        ]);
        
        $writeConcern = new MongoDB\Driver\WriteConcern(0, 10000);
        $result = $manager->executeBulkWrite($collection, $bulk, $writeConcern);
        
        if (! $result->getWriteErrors()) {
            echo "success";
        } else {
            foreach ($result->getWriteErrors() as $writeError) {
                printf("Operation#%d: %s (%d)\n", $writeError->getIndex(), $writeError->getMessage(), $writeError->getCode());
                echo $writeError->getMessage();
            }
        }
    } 

} 

?> 