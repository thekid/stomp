<?php namespace org\codehaus\stomp;

  class Subscription extends \lang\Object {
    protected $id         = NULL;
    protected $destination= NULL;
    protected $ackMode    = NULL;
    protected $selector   = NULL;

    protected $conn       = NULL;

    /**
     * Constructor
     *
     * @param   string destination
     * @param   string ackMode default AckMode::INDIVIDUAL
     * @param   string selector default NULL
     * @throws  lang.IllegalArgumentExcpetion
     */
    public function __construct($destination, $ackMode= AckMode::INDIVIDUAL, $selector= NULL) {
      if (empty($destination)) {
        throw new \lang\IllegalArgumentException('Invalid destination given: "'.$destination.'"');
      }

      $this->destination= $destination;

      if (!in_array($ackMode, array(AckMode::AUTO, AckMode::CLIENT, AckMode::INDIVIDUAL))) {
        throw new \lang\IllegalArgumentException('Invalid ackMode given: "'.$ackMode.'"');
      }

      $this->ackMode= $ackMode;
      $this->selector= $selector;
    }

    public function getId() {
      return $this->id;
    }

    public function subscribe(StompConnection $conn) {
      try {
        $this->id= uniqid('xp.stomp.subscription.');

        $frame= new \org\codehaus\stomp\frame\SubscribeFrame($this->destination, $this->ackMode, $this->selector);
        $frame->setId($this->id);

        $this->conn= $conn;

        $this->conn->sendFrame($frame);
      } catch (\lang\Throwable $t) {
        $this->id= NULL;
        throw $t;
      }
    }

    public function unsubscribe() {
      if (!$this->id) {
        throw new \lang\IllegalStateException('Cannot unsubscribe when not subscribed.');
      }

      $this->conn->sendFrame(new frame\UnsubscribeFrame(NULL, $this->id));
      $this->id= NULL;
      $this->conn= NULL;
    }

    public function __destruct() {
      if ($this->id && $this->conn instanceof StompConnection) {
        $this->unsubscribe();
      }
    }
  }
?>