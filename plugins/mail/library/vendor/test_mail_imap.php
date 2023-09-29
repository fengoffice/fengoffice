<?php
include 'autoload.php'; 


use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

// or use an array of options instead
$cm = new ClientManager();

/** @var \Webklex\PHPIMAP\Client $client */
$client = $cm->account('account_identifier');

$access_token = 'EwBYA+l3BAAUnQP8Jfa2FYxR0AX7HsEZwOdWa28AAQrkgmMoxGykVHYIo58KG1z9v5Cvj3eTB8SkKcaBHe7wmWei+yZdpKmOhw2lfSzN5MW3KI5IsqclG9VjrhZKcAB6QjpvjbgudmY6YVHRiJ+jfaRlWHpuLb0gwDXP5V3c4khxRcMipGdBTMXknnzB8gWBpOvuT+DPK1emc9rg5N439xuFqybN2pJISL0nKSeuAx9IWk2e8xrlZHQows/o/A6dIXNVZGrkezHkCzrxO6VkWBS/UVrIpWiUeAUYHMAJSOz4uczyUzhQ32JEWxFUfq24rB5hVmp1VWtDfVsptwdCcwDUUm9Al2fca5lieVp9tUcrrv1FVJLbqq0Zf3HHqbwDZgAACMVEggj2qmTxKAJs4VieGMYStP9GRrLBu0Rmm9VkfdjgJpNzBslFPmWMdx+Y4+JtevTlRojpKcJbN2UKZuU5zMrSPXNEOUELc5d9qm1oO1j2Cg1LFlrL4pJltG2SXIl7osC6r+uY6JpGK7TwakJ8YKBmxpe9uaMWl6EveOXnKX6oicm++lNmkqgulE86xR2OwTeD7PWgOEr7SOE3eKGc/72Un2jzpprWri+Wk8q4BuuCC15dozCxwH/TuFxk+kJrOwjZt3xdc/EpBEc9XNWbHi3pwTYvafpNrrbYbMZ2s31A+6jbRXynhfiroKy+wgblA1t5U7IQuS+XEILmoc9LGuCDLwopK7tO2iy1hOFKIr8VzZfOKy5xrWWc4KCRmXV3ShCYQ/+WmXi912vaA55i8v0TiJTr5pmY30DPaB+ypoORFqVq+sAEz1lgY2rQUgbjQ64DhWVYVxk/8BJNP2AMolY96P/nL94lMp20LtwVddwb2Ojm8V1m116+Dsvl1+K5toCuRQsixxqta/v47hLBR2sL6nHm5uONjYspAOjt7fxRRJmUVA9pCcMacIxBDFFG/xzrarsFgXZ+EV6E4DQwpYv/tfDbVxtfSk3fop11jvuOaUJwN0AQdwOecNgMbKC3ymSEasYTbej3nUt+jPu3ASbXPdZxgqayXgzWJtqz3vkpXLsWqNEtylE31SFJdUUIslpMCuegIb/D0n7e2bT5PtOAID0G+3x0IHuzVQ8GLazDJ3tYAg==';


// or create a new instance manually
$client = $cm->make([
    'host'          => 'outlook.office365.com',
    'port'          => 993,
    'encryption'    => 'ssl',
    'validate_cert' => false,
    'username'      => 'zonis7@outlook.com',
    'password'      => $access_token,
    'protocol'      => 'imap',
    'authentication' => 'oauth'
]);


//Connect to the IMAP Server
$client->connect();

//Get all Mailboxes
/** @var \Webklex\PHPIMAP\Support\FolderCollection $folders */
$folders = $client->getFolders();

//Loop through every Mailbox
/** @var \Webklex\PHPIMAP\Folder $folder */
foreach($folders as $folder){

    //Get all Messages of the current Mailbox $folder
    /** @var \Webklex\PHPIMAP\Support\MessageCollection $messages */
    $messages = $folder->messages()->all()->get();

    /** @var \Webklex\PHPIMAP\Message $message */
    foreach($messages as $message){
        echo $message->getSubject().'<br />';
        echo 'Attachments: '.$message->getAttachments()->count().'<br />';
        echo $message->getHTMLBody();

        //Move the current Message to 'INBOX.read'
        if($message->move('INBOX.read') == true){
            echo 'Message has ben moved';
        }else{
            echo 'Message could not be moved';
        }
    }
}