<?php
/**
 *	自定义 session 文件存储
 */

class sessionHandle {

    protected $savePath  = '';
    protected $sessID = null;
	protected $readhandle = false;
	protected $readfail = false;//是否因为不可读而导致读失败（出现这种情况需要控制写session文件，让写文件也失败，否则会造成session文件写入数据异常[未读到的数据丢失]）
    protected $debuglogfile = '/tmp/session_debug.txt';
    protected $processid = null;
    protected $debug = false;

    /**
     * 打开Session
     * @access public
     * @param string $savePath
     * @param mixed $sessName
     */
    public function open($savePath, $sessName) {
        $this->savePath = realpath($savePath);
        if($this->debug) {
            $this->processid = getmypid();
            $loginfo = "pid={$this->processid} session_open {$sessName} {$this->savePath}";
            $this->debugger($loginfo);
        }
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
	public function close() {
		//先释放排它锁
		if($this->readhandle) {
			flock($this->readhandle, LOCK_UN);
			fclose($this->readhandle);
		}
		if($this->debug) {
			$loginfo = "pid={$this->processid} session_close {$this->sessID}";
			$this->debugger($loginfo);
		}
		return true;
	}

    /**
     * 读取Session
     * @access public
     * @param string $sessID
     */
	public function read($sessID) {
		$this->sessID = $sessID;
		$is_readable = true;
		$counter = $times = 0;//计次 计时
		touch("{$this->savePath}/sess_{$sessID}");
		while(!is_readable("{$this->savePath}/sess_{$sessID}")) {
			$is_readable = false;
			clearstatcache();
			$delta = 1000*rand(1,5);//1ms ~ 5ms
            if($counter++ > 200 || $times > 300000) {
				break;
            }
            $times += $delta;
            usleep($delta);
		}
		if(!$is_readable) {
			if($this->debug) {
			    $loginfo = "pid={$this->processid} session_read !is_readable fail {$sessID}";
			    $this->debugger($loginfo);
			}
			$this->readfail = true;
			return '';
		}
		if(false === ($this->readhandle = @fopen("{$this->savePath}/sess_{$sessID}", 'rb'))) {
            if($this->debug) {
			    $loginfo = "pid={$this->processid} session_read fopen fail {$sessID}";
			    $this->debugger($loginfo);
			}
			return '';
		}
		$contents = '';
		if(flock($this->readhandle, LOCK_EX)) {//排它锁
			while(!feof($this->readhandle)) {
				$contents .= fread($this->readhandle, 1024);
			}
		}
		if($this->debug) {
			if(strlen($contents) < 1) {
				$loginfo = "pid={$this->processid} session_read empty {$sessID}";
			} else {
				$loginfo = "pid={$this->processid} session_read strlen {$sessID}";
			}
			$this->debugger($loginfo);
		}
		return $contents;
	}

    /**
     * 写入Session
     * @access public
     * @param string $sessID
     * @param String $sessData
     */
	public function write($sessID, $sessData) {
		if($this->readfail) {
			if($this->debug) {
			    $loginfo = "pid={$this->processid} session_write readfail fail {$sessID}";
			    $this->debugger($loginfo);
			}
			return false;
		}
        if (!file_exists($this->savePath) || !is_dir($this->savePath)) {
            mkdir($this->savePath, 0775);
        }
		//read的时候已经设置了排它锁这里不再需要设置文件锁可以直接写
		if(false === ($handle = @fopen("{$this->savePath}/sess_{$sessID}", 'wb'))) {
			if($this->debug) {
			    $loginfo = "pid={$this->processid} session_write fopen fail {$sessID}";
			    $this->debugger($loginfo);
			}
			return false;
		}
		$resu = fwrite($handle, $sessData, strlen($sessData));
		if($this->debug) {
			if($resu === false) {
				$loginfo = "pid={$this->processid} session_write fail {$sessID}";
			} else {
				if(strlen($sessData) < 1) {
					$loginfo = "pid={$this->processid} session_write empty {$sessID}";
				} else {
					$loginfo = "pid={$this->processid} session_write strlen {$sessID}";
				}
			}
			$this->debugger($loginfo);
		}
		fclose($handle);
		return $resu === false ? false : true;
	}

    /**
     * 删除Session
     * @access public
     * @param string $sessID
     */
	public function destroy($sessID) {
		if($this->debug) {
			$loginfo = "pid={$this->processid} session_destroy {$sessID}";
			$this->debugger($loginfo);
		}
		return @unlink("{$this->savePath}/sess_{$sessID}");
	}

    /**
     * Session 垃圾回收
     * @access public
     * @param string $sessMaxLifeTime
     */
	public function gc($sessMaxLifeTime) {
		if($this->debug) {
			$loginfo = "pid={$this->processid} session_gc {$sessID}";
			$this->debugger($loginfo);
		}
		foreach (glob("{$this->savePath}/sess_*") as $filename) {
			if(filemtime($filename) + $sessMaxLifeTime < time()) {
				@unlink($filename);
			}
		}
		return true;
	}

	public function debugger($string, $wmode = 'append') {
		return file_put_contents($this->debuglogfile, "[".date("Y-m-d H:i:s")." ".date_default_timezone_get()."] {$string}\r\n", $wmode == 'append' ? FILE_APPEND : 0);
	}
}
