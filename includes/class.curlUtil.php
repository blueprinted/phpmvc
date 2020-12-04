<?php
/**
 *  curl实现http请求类
 *  @date 2018-08-29 15:55:02
 */
/*
curl的选项参数
// boolean options
CURLOPT_AUTOREFERER                                //TRUE 时将根据 Location: 重定向时，自动设置 header 中的Referer:信息。
CURLOPT_BINARYTRANSFER                             //设为 TRUE ，将在启用 CURLOPT_RETURNTRANSFER 时，返回原生的（Raw）输出。从 PHP 5.1.3 开始，此选项不再有效果：使用 CURLOPT_RETURNTRANSFER 后总是会返回原生的（Raw）内容。
CURLOPT_COOKIESESSION                              //设为 TRUE 时将开启新的一次 cookie 会话。它将强制 libcurl 忽略之前会话时存的其他 cookie。 libcurl 在默认状况下无论是否为会话，都会储存、加载所有 cookie。会话 cookie 是指没有过期时间，只存活在会话之中。
CURLOPT_CERTINFO                                   //TRUE 将在安全传输时输出 SSL 证书信息到 STDERR。在 cURL 7.19.1 中添加。 PHP 5.3.2 后有效。 需要开启 CURLOPT_VERBOSE 才有效。
CURLOPT_CONNECT_ONLY                               //TRUE 将让库执行所有需要的代理、验证、连接过程，但不传输数据。此选项用于 HTTP、SMTP 和 POP3。在 cURL 7.15.2 中添加。 PHP 5.5.0 起有效。
CURLOPT_CRLF                                       //启用时将Unix的换行符转换成回车换行符。
CURLOPT_DNS_USE_GLOBAL_CACHE                       //TRUE 会启用一个全局的DNS缓存。此选项非线程安全的，默认已开启。
CURLOPT_FAILONERROR                                //当 HTTP 状态码大于等于 400，TRUE 将将显示错误详情。 默认情况下将返回页面，忽略 HTTP 代码。
CURLOPT_SSL_FALSESTART                             //TRUE 开启 TLS False Start （一种 TLS 握手优化方式）。cURL 7.42.0 中添加。自 PHP 7.0.7 起有效。
CURLOPT_FILETIME                                   //TRUE 时，会尝试获取远程文档中的修改时间信息。 信息可通过curl_getinfo()函数的CURLINFO_FILETIME 选项获取。
CURLOPT_FOLLOWLOCATION                             //TRUE 时将会根据服务器返回 HTTP 头中的 "Location: " 重定向。（注意：这是递归的，"Location: " 发送几次就重定向几次，除非设置了 CURLOPT_MAXREDIRS，限制最大重定向次数。）。
CURLOPT_FORBID_REUSE                               //TRUE 在完成交互以后强制明确的断开连接，不能在连接池中重用。
CURLOPT_FRESH_CONNECT                              //TRUE 强制获取一个新的连接，而不是缓存中的连接。
CURLOPT_FTP_USE_EPRT                               //TRUE 时，当 FTP 下载时，使用 EPRT (和 LPRT)命令。 设置为 FALSE 时禁用 EPRT 和 LPRT，仅仅使用PORT 命令。
CURLOPT_FTP_USE_EPSV                               //TRUE 时，在FTP传输过程中，回到 PASV 模式前，先尝试 EPSV 命令。设置为 FALSE 时禁用 EPSV。
CURLOPT_FTP_CREATE_MISSING_DIRS                    //TRUE 时，当 ftp 操作不存在的目录时将创建它。
CURLOPT_FTPAPPEND                                  //TRUE 为追加写入文件，而不是覆盖。
CURLOPT_TCP_NODELAY                                //TRUE 时禁用 TCP 的 Nagle 算法，就是减少网络上的小包数量。PHP 5.2.1 有效，编译时需要 libcurl 7.11.2 及以上。
CURLOPT_FTPASCII                                   //CURLOPT_TRANSFERTEXT 的别名。
CURLOPT_TRANSFERTEXT                               //TRUE 对 FTP 传输使用 ASCII 模式。对于LDAP，它检索纯文本信息而非 HTML。在 Windows 系统上，系统不会把 STDOUT 设置成二进制 模式。
CURLOPT_FTPLISTONLY                                //TRUE 时只列出 FTP 目录的名字。
CURLOPT_HEADER                                     //启用时会将头文件的信息作为数据流输出。
CURLINFO_HEADER_OUT                                //TRUE 时追踪句柄的请求字符串。从 PHP 5.1.3 开始可用。CURLINFO_ 的前缀是有意的(intentional)。
CURLOPT_HTTPGET                                    //TRUE 时会设置 HTTP 的 method 为 GET，由于默认是 GET，所以只有 method 被修改时才需要这个选项。
CURLOPT_HTTPPROXYTUNNEL                            //TRUE 会通过指定的 HTTP 代理来传输。
CURLOPT_MUTE                                       //TRUE 时将完全静默，无论是何 cURL 函数。在 cURL 7.15.5 中移出（可以使用 CURLOPT_RETURNTRANSFER 作为代替）
CURLOPT_NETRC                                      //TRUE 时，在连接建立时，访问~/.netrc文件获取用户名和密码来连接远程站点。
CURLOPT_NOBODY                                     //TRUE 时将不输出 BODY 部分。同时 Mehtod 变成了 HEAD。修改为 FALSE 时不会变成 GET。
CURLOPT_NOPROGRESS                                 //TRUE 时关闭 cURL 的传输进度。Note:PHP 默认自动设置此选项为 TRUE，只有为了调试才需要改变设置。
CURLOPT_NOSIGNAL                                   //TRUE 时忽略所有的 cURL 传递给 PHP 进行的信号。在 SAPI 多线程传输时此项被默认启用，所以超时选项仍能使用。
CURLOPT_PATH_AS_IS                                 //TRUE 不处理 dot dot sequences （即 ../ ）。cURL 7.42.0 时被加入。 PHP 7.0.7 起有效。
CURLOPT_PIPEWAIT                                   //TRUE 则等待 pipelining/multiplexing。cURL 7.43.0 时被加入。 PHP 7.0.7 起有效。
CURLOPT_POST                                       //TRUE 时会发送 POST 请求，类型为：application/x-www-form-urlencoded，是 HTML 表单提交时最常见的一种。
CURLOPT_PUT                                        //TRUE 时允许 HTTP 发送文件。要被 PUT 的文件必须在 CURLOPT_INFILE和CURLOPT_INFILESIZE 中设置。
CURLOPT_RETURNTRANSFER                             //TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
CURLOPT_SAFE_UPLOAD                                //TRUE 禁用 @ 前缀在 CURLOPT_POSTFIELDS 中发送文件。 意味着 @ 可以在字段中安全得使用了。 可使用 CURLFile 作为上传的代替。PHP 5.5.0 中添加，默认值 FALSE。 PHP 5.6.0 改默认值为 TRUE。. PHP 7 删除了此选项， 必须使用 CURLFile interface 来上传文件。
CURLOPT_SASL_IR                                    //TRUE 开启，收到首包(first packet)后发送初始的响应(initial response)。cURL 7.31.10 中添加，自 PHP 7.0.7 起有效。
CURLOPT_SSL_ENABLE_ALPN                            //FALSE 禁用 SSL 握手中的 ALPN (如果 SSL 后端的 libcurl 内建支持) 用于协商到 http2。cURL 7.36.0 中增加， PHP 7.0.7 起有效。
CURLOPT_SSL_VERIFYPEER                             //FALSE 禁止 cURL 验证对等证书（peer's certificate）。要验证的交换证书可以在 CURLOPT_CAINFO 选项中设置，或在 CURLOPT_CAPATH中设置证书目录。自cURL 7.10开始默认为 TRUE。从 cURL 7.10开始默认绑定安装。
CURLOPT_SSL_VERIFYSTATUS                           //TRUE 验证证书状态。cURL 7.41.0 中添加， PHP 7.0.7 起有效。
CURLOPT_TCP_FASTOPEN                               //TRUE 开启 TCP Fast Open。cURL 7.49.0 中添加， PHP 7.0.7 起有效。
CURLOPT_TFTP_NO_OPTIONS                            //TRUE 不发送 TFTP 的 options 请求。自 cURL 7.48.0 添加， PHP 7.0.7 起有效。
CURLOPT_UNRESTRICTED_AUTH                          //TRUE 在使用CURLOPT_FOLLOWLOCATION重定向 header 中的多个 location 时继续发送用户名和密码信息，哪怕主机名已改变。
CURLOPT_UPLOAD                                     //TRUE 准备上传。
CURLOPT_VERBOSE                                    //TRUE 会输出所有的信息，写入到STDERR，或在CURLOPT_STDERR中指定的文件。
// integer options
CURLOPT_BUFFERSIZE                                 //每次读入的缓冲的尺寸。当然不保证每次都会完全填满这个尺寸。在cURL 7.10中被加入。
CURLOPT_CLOSEPOLICY                                //CURLCLOSEPOLICY_* 中的一个。Note:此选项已被废弃，它不会被实现，永远不会有效果啦。PHP 5.6.0 中移除。
CURLOPT_CONNECTTIMEOUT                             //在尝试连接时等待的秒数。设置为0，则无限等待。
CURLOPT_CONNECTTIMEOUT_MS                          //尝试连接等待的时间，以毫秒为单位。设置为0，则无限等待。 如果 libcurl 编译时使用系统标准的名称解析器（ standard system name resolver），那部分的连接仍旧使用以秒计的超时解决方案，最小超时时间还是一秒钟。  在 cURL 7.16.2 中被加入。从 PHP 5.2.3 开始可用。
CURLOPT_DNS_CACHE_TIMEOUT                          //设置在内存中缓存 DNS 的时间，默认为120秒（两分钟）。
CURLOPT_EXPECT_100_TIMEOUT_MS                      //超时预计：100毫秒内的 continue 响应 默认为 1000 毫秒。  cURL 7.36.0 中添加，自 PHP 7.0.7 有效。
CURLOPT_FTPSSLAUTH                                 //FTP验证方式（启用的时候）：CURLFTPAUTH_SSL (首先尝试SSL)，CURLFTPAUTH_TLS (首先尝试TLS)或CURLFTPAUTH_DEFAULT (让cURL 自个儿决定)。  在 cURL 7.12.2 中被加入。
CURLOPT_HEADEROPT                                  //How to deal with headers. One of the following constants: CURLHEADER_UNIFIED: the headers specified in CURLOPT_HTTPHEADER will be used in requests both to servers and proxies. With this option enabled, CURLOPT_PROXYHEADER will not have any effect. CURLHEADER_SEPARATE: makes CURLOPT_HTTPHEADER headers only get sent to a server and not to a proxy. Proxy headers must be set with CURLOPT_PROXYHEADER to get used. Note that if a non-CONNECT request is sent to a proxy, libcurl will send both server headers and proxy headers. When doing CONNECT, libcurl will send CURLOPT_PROXYHEADER headers only to the proxy and then CURLOPT_HTTPHEADER headers only to the server. Defaults to CURLHEADER_SEPARATE as of cURL 7.42.1, and CURLHEADER_UNIFIED before.  Added in cURL 7.37.0. Available since PHP 7.0.7.
CURLOPT_HTTP_VERSION                               //CURL_HTTP_VERSION_NONE (默认值，让 cURL 自己判断使用哪个版本)，CURL_HTTP_VERSION_1_0 (强制使用 HTTP/1.0)或CURL_HTTP_VERSION_1_1 (强制使用 HTTP/1.1)。
CURLOPT_HTTPAUTH                                   //使用的 HTTP 验证方法。选项有： CURLAUTH_BASIC、 CURLAUTH_DIGEST、 CURLAUTH_GSSNEGOTIATE、 CURLAUTH_NTLM、 CURLAUTH_ANY和 CURLAUTH_ANYSAFE。可以使用 | 位域(OR)操作符结合多个值，cURL 会让服务器选择受支持的方法，并选择最好的那个。CURLAUTH_ANY是 CURLAUTH_BASIC | CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM 的别名。CURLAUTH_ANYSAFE 是 CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM 的别名。
CURLOPT_INFILESIZE                                 //希望传给远程站点的文件尺寸，字节(byte)为单位。 注意无法用这个选项阻止 libcurl 发送更多的数据，确切发送什么取决于 CURLOPT_READFUNCTION。
CURLOPT_LOW_SPEED_LIMIT                            //传输速度，每秒字节（bytes）数，根据CURLOPT_LOW_SPEED_TIME秒数统计是否因太慢而取消传输。
CURLOPT_LOW_SPEED_TIME                             //当传输速度小于CURLOPT_LOW_SPEED_LIMIT时(bytes/sec)，PHP会判断是否因太慢而取消传输。
CURLOPT_MAXCONNECTS                                //允许的最大连接数量。达到限制时，会通过CURLOPT_CLOSEPOLICY决定应该关闭哪些连接。
CURLOPT_MAXREDIRS                                  //指定最多的 HTTP 重定向次数，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的。
CURLOPT_PORT                                       //用来指定连接端口。
CURLOPT_POSTREDIR                                  //位掩码， 1 (301 永久重定向), 2 (302 Found) 和 4 (303 See Other) 设置 CURLOPT_FOLLOWLOCATION 时，什么情况下需要再次 HTTP POST 到重定向网址。  cURL 7.19.1 中添加，PHP 5.3.2 开始可用。
CURLOPT_PROTOCOLS                                  //CURLPROTO_*的位掩码。 启用时，会限制 libcurl 在传输过程中可使用哪些协议。 这将允许你在编译libcurl时支持众多协议，但是限制只用允许的子集。默认 libcurl 将使用所有支持的协议。 参见CURLOPT_REDIR_PROTOCOLS。可用的协议选项为： CURLPROTO_HTTP、 CURLPROTO_HTTPS、 CURLPROTO_FTP、 CURLPROTO_FTPS、 CURLPROTO_SCP、 CURLPROTO_SFTP、 CURLPROTO_TELNET、 CURLPROTO_LDAP、 CURLPROTO_LDAPS、 CURLPROTO_DICT、 CURLPROTO_FILE、 CURLPROTO_TFTP、 CURLPROTO_ALL。在 cURL 7.19.4 中被加入。
CURLOPT_PROXYAUTH                                  //HTTP 代理连接的验证方式。使用在CURLOPT_HTTPAUTH中的位掩码。 当前仅仅支持 CURLAUTH_BASIC和CURLAUTH_NTLM。  在 cURL 7.10.7 中被加入。
CURLOPT_PROXYPORT                                  //代理服务器的端口。端口也可以在CURLOPT_PROXY中设置。
CURLOPT_PROXYTYPE                                  //可以是 CURLPROXY_HTTP (默认值) CURLPROXY_SOCKS4、 CURLPROXY_SOCKS5、 CURLPROXY_SOCKS4A 或 CURLPROXY_SOCKS5_HOSTNAME。    在 cURL 7.10 中被加入。
CURLOPT_REDIR_PROTOCOLS                            //CURLPROTO_* 值的位掩码。如果被启用，位掩码会限制 libcurl 在 CURLOPT_FOLLOWLOCATION开启时，使用的协议。 默认允许除 FILE 和 SCP 外所有协议。 这和 7.19.4 前的版本无条件支持所有支持的协议不同。关于协议常量，请参照CURLOPT_PROTOCOLS。    在 cURL 7.19.4 中被加入。
CURLOPT_RESUME_FROM                                //在恢复传输时，传递字节为单位的偏移量（用来断点续传）。
CURLOPT_SSL_OPTIONS                                //Set SSL behavior options, which is a bitmask of any of the following constants: CURLSSLOPT_ALLOW_BEAST: do not attempt to use any workarounds for a security flaw in the SSL3 and TLS1.0 protocols. CURLSSLOPT_NO_REVOKE: disable certificate revocation checks for those SSL backends where such behavior is present. Added in cURL 7.25.0. Available since PHP 7.0.7.
CURLOPT_SSL_VERIFYHOST                             //设置为 1 是检查服务器SSL证书中是否存在一个公用名(common name)。译者注：公用名(Common Name)一般来讲就是填写你将要申请SSL证书的域名 (domain)或子域名(sub domain)。 设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配。 0 为不检查名称。 在生产环境中，这个值应该是 2（默认值）。   值 1 的支持在 cURL 7.28.1 中被删除了。
CURLOPT_SSLVERSION                                 //CURL_SSLVERSION_DEFAULT (0), CURL_SSLVERSION_TLSv1 (1), CURL_SSLVERSION_SSLv2 (2), CURL_SSLVERSION_SSLv3 (3), CURL_SSLVERSION_TLSv1_0 (4), CURL_SSLVERSION_TLSv1_1 (5) ， CURL_SSLVERSION_TLSv1_2 (6) 中的其中一个。Note:你最好别设置这个值，让它使用默认值。 设置为 2 或 3 比较危险，在 SSLv2 和 SSLv3 中有弱点存在。
CURLOPT_STREAM_WEIGHT                              //设置 stream weight 数值 ( 1 和 256 之间的数字)。cURL 7.46.0 中添加，自 PHP 7.0.7 起有效。
CURLOPT_TIMECONDITION                              //设置如何对待 CURLOPT_TIMEVALUE。 使用 CURL_TIMECOND_IFMODSINCE，仅在页面 CURLOPT_TIMEVALUE 之后修改，才返回页面。没有修改则返回 "304 Not Modified" 头，假设设置了 CURLOPT_HEADER 为 TRUE。CURL_TIMECOND_IFUNMODSINCE则起相反的效果。 默认为 CURL_TIMECOND_IFMODSINCE。
CURLOPT_TIMEOUT                                    //允许 cURL 函数执行的最长秒数。
CURLOPT_TIMEOUT_MS                                 //设置cURL允许执行的最长毫秒数。 如果 libcurl 编译时使用系统标准的名称解析器（ standard system name resolver），那部分的连接仍旧使用以秒计的超时解决方案，最小超时时间还是一秒钟。 在 cURL 7.16.2 中被加入。从 PHP 5.2.3 起可使用。
CURLOPT_TIMEVALUE                                  //秒数，从 1970年1月1日开始。这个时间会被 CURLOPT_TIMECONDITION使。默认使用CURL_TIMECOND_IFMODSINCE。
CURLOPT_MAX_RECV_SPEED_LARGE                       //如果下载速度超过了此速度(以每秒字节数来统计) ，即传输过程中累计的平均数，传输就会降速到这个参数的值。默认不限速。 cURL 7.15.5 中添加， PHP 5.4.0 有效。
CURLOPT_MAX_SEND_SPEED_LARGE                       //如果上传的速度超过了此速度(以每秒字节数来统计)，即传输过程中累计的平均数 ，传输就会降速到这个参数的值。默认不限速。    cURL 7.15.5 中添加， PHP 5.4.0 有效。
CURLOPT_SSH_AUTH_TYPES                             //A bitmask consisting of one or more of CURLSSH_AUTH_PUBLICKEY, CURLSSH_AUTH_PASSWORD, CURLSSH_AUTH_HOST, CURLSSH_AUTH_KEYBOARD. Set to CURLSSH_AUTH_ANY to let libcurl pick one.   cURL 7.16.1 中添加。
CURLOPT_IPRESOLVE                                  //允许程序选择想要解析的 IP 地址类别。只有在地址有多种 ip 类别的时候才能用，可以的值有： CURL_IPRESOLVE_WHATEVER、 CURL_IPRESOLVE_V4、 CURL_IPRESOLVE_V6，默认是 CURL_IPRESOLVE_WHATEVER。 cURL 7.10.8 中添加。
CURLOPT_FTP_FILEMETHOD                             //告诉 curl 使用哪种方式来获取 FTP(s) 服务器上的文件。可能的值有： CURLFTPMETHOD_MULTICWD、 CURLFTPMETHOD_NOCWD 和 CURLFTPMETHOD_SINGLECWD。 cURL 7.15.1 中添加， PHP 5.3.0 起有效。
// string options
CURLOPT_CAINFO                                     //一个保存着1个或多个用来让服务端验证的证书的文件名。这个参数仅仅在和CURLOPT_SSL_VERIFYPEER一起使用时才有意义。 .   可能需要绝对路径。
CURLOPT_CAPATH                                     //一个保存着多个CA证书的目录。这个选项是和CURLOPT_SSL_VERIFYPEER一起使用的。
CURLOPT_COOKIE                                     //设定 HTTP 请求中"Cookie: "部分的内容。多个 cookie 用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")。
CURLOPT_COOKIEFILE                                 //包含 cookie 数据的文件名，cookie 文件的格式可以是 Netscape 格式，或者只是纯 HTTP 头部风格，存入文件。如果文件名是空的，不会加载 cookie，但 cookie 的处理仍旧启用。
CURLOPT_COOKIEJAR                                  //连接结束后，比如，调用 curl_close 后，保存 cookie 信息的文件。
CURLOPT_CUSTOMREQUEST                              //HTTP 请求时，使用自定义的 Method 来代替"GET"或"HEAD"。对 "DELETE" 或者其他更隐蔽的 HTTP 请求有用。 有效值如 "GET"，"POST"，"CONNECT"等等；也就是说，不要在这里输入整行 HTTP 请求。例如输入"GET /index.html HTTP/1.0\r\n\r\n"是不正确的。Note:不确定服务器支持这个自定义方法则不要使用它。
CURLOPT_DEFAULT_PROTOCOL                           //URL不带协议的时候，使用的默认协议。cURL 7.45.0 中添加，自 PHP 7.0.7 起有效。
CURLOPT_DNS_INTERFACE                              //Set the name of the network interface that the DNS resolver should bind to. This must be an interface name (not an address). Added in cURL 7.33.0. Available since PHP 7.0.7.
CURLOPT_DNS_LOCAL_IP4                              //Set the local IPv4 address that the resolver should bind to. The argument should contain a single numerical IPv4 address as a string. Added in cURL 7.33.0. Available since PHP 7.0.7.
CURLOPT_DNS_LOCAL_IP6                              //Set the local IPv6 address that the resolver should bind to. The argument should contain a single numerical IPv6 address as a string. Added in cURL 7.33.0. Available since PHP 7.0.7.
CURLOPT_EGDSOCKET                                  //类似CURLOPT_RANDOM_FILE，除了一个Entropy Gathering Daemon套接字。
CURLOPT_ENCODING                                   //HTTP请求头中"Accept-Encoding: "的值。 这使得能够解码响应的内容。 支持的编码有"identity"，"deflate"和"gzip"。如果为空字符串""，会发送所有支持的编码类型。 在 cURL 7.10 中被加入。
CURLOPT_FTPPORT                                    //这个值将被用来获取供FTP"PORT"指令所需要的IP地址。 "PORT" 指令告诉远程服务器连接到我们指定的IP地址。这个字符串可以是纯文本的IP地址、主机名、一个网络接口名（UNIX下）或者只是一个'-'来使用默认的 IP 地址。
CURLOPT_INTERFACE                                  //发送的网络接口（interface），可以是一个接口名、IP 地址或者是一个主机名。
CURLOPT_KEYPASSWD                                  //使用 CURLOPT_SSLKEY 或 CURLOPT_SSH_PRIVATE_KEYFILE 私钥时候的密码。在 cURL 7.16.1 中添加。
CURLOPT_KRB4LEVEL                                  //KRB4 (Kerberos 4) 安全级别。下面的任何值都是有效的(从低到高的顺序)："clear"、"safe"、"confidential"、"private".。如果字符串以上这些，将使用"private"。 这个选项设置为 NULL 时将禁用 KRB4 安全认证。目前 KRB4 安全认证只能用于 FTP 传输。
CURLOPT_LOGIN_OPTIONS                              //Can be used to set protocol specific login options, such as the preferred authentication mechanism via "AUTH=NTLM" or "AUTH=*", and should be used in conjunction with the CURLOPT_USERNAME option.    Added in cURL 7.34.0. Available since PHP 7.0.7.
CURLOPT_PINNEDPUBLICKEY                            //Set the pinned public key. The string can be the file name of your pinned public key. The file format expected is "PEM" or "DER". The string can also be any number of base64 encoded sha256 hashes preceded by "sha256//" and separated by ";".   Added in cURL 7.39.0. Available since PHP 7.0.7.
CURLOPT_POSTFIELDS                                 //全部数据使用HTTP协议中的 "POST" 操作来发送。 要发送文件，在文件名前面加上@前缀并使用完整路径。 文件类型可在文件名后以 ';type=mimetype' 的格式指定。 这个参数可以是 urlencoded 后的字符串，类似'para1=val1&para2=val2&...'，也可以使用一个以字段名为键值，字段数据为值的数组。 如果value是一个数组，Content-Type头将会被设置成multipart/form-data。 从 PHP 5.2.0 开始，使用 @ 前缀传递文件时，value 必须是个数组。 从 PHP 5.5.0 开始, @ 前缀已被废弃，文件可通过 CURLFile 发送。 设置 CURLOPT_SAFE_UPLOAD 为 TRUE 可禁用 @ 前缀发送文件，以增加安全性。
CURLOPT_PRIVATE                                    //Any data that should be associated with this cURL handle. This data can subsequently be retrieved with the CURLINFO_PRIVATE option of curl_getinfo(). cURL does nothing with this data. When using a cURL multi handle, this private data is typically a unique key to identify a standard cURL handle.    Added in cURL 7.10.3.
CURLOPT_PROXY   HTTP                                 //代理通道。
CURLOPT_PROXY_SERVICE_NAME                         //代理验证服务的名称。 cURL 7.34.0 中添加，PHP 7.0.7 起有效。
CURLOPT_PROXYUSERPWD                               //一个用来连接到代理的"[username]:[password]"格式的字符串。
CURLOPT_RANDOM_FILE                                //一个被用来生成 SSL 随机数种子的文件名。
CURLOPT_RANGE                                      //以"X-Y"的形式，其中X和Y都是可选项获取数据的范围，以字节计。HTTP传输线程也支持几个这样的重复项中间用逗号分隔如"X-Y,N-M"。
CURLOPT_REFERER                                    //在HTTP请求头中"Referer: "的内容。
CURLOPT_SERVICE_NAME                               //验证服务的名称 cURL 7.43.0 起添加，自 PHP 7.0.7 有效。
CURLOPT_SSH_HOST_PUBLIC_KEY_MD5                    //包含 32 位长的 16 进制数值。这个字符串应该是远程主机公钥（public key） 的 MD5 校验值。在不匹配的时候 libcurl 会拒绝连接。 此选项仅用于 SCP 和 SFTP 的传输。   cURL 7.17.1 中添加。
CURLOPT_SSH_PUBLIC_KEYFILE                         //The file name for your public key. If not used, libcurl defaults to $HOME/.ssh/id_dsa.pub if the HOME environment variable is set, and just "id_dsa.pub" in the current directory if HOME is not set.  Added in cURL 7.16.1.
CURLOPT_SSH_PRIVATE_KEYFILE                        //The file name for your private key. If not used, libcurl defaults to $HOME/.ssh/id_dsa if the HOME environment variable is set, and just "id_dsa" in the current directory if HOME is not set. If the file is password-protected, set the password with CURLOPT_KEYPASSWD. Added in cURL 7.16.1.
CURLOPT_SSL_CIPHER_LIST                            //一个SSL的加密算法列表。例如RC4-SHA和TLSv1都是可用的加密列表。
CURLOPT_SSLCERT                                    //一个包含 PEM 格式证书的文件名。
CURLOPT_SSLCERTPASSWD                              //使用CURLOPT_SSLCERT证书需要的密码。
CURLOPT_SSLCERTTYPE                                //证书的类型。支持的格式有"PEM" (默认值), "DER"和"ENG"。  在 cURL 7.9.3中 被加入。
CURLOPT_SSLENGINE                                  //用来在CURLOPT_SSLKEY中指定的SSL私钥的加密引擎变量。
CURLOPT_SSLENGINE_DEFAULT                          //用来做非对称加密操作的变量。
CURLOPT_SSLKEY                                     //包含 SSL 私钥的文件名。
CURLOPT_SSLKEYPASSWD                               //在 CURLOPT_SSLKEY中指定了的SSL私钥的密码。Note:由于这个选项包含了敏感的密码信息，记得保证这个PHP脚本的安全。
CURLOPT_SSLKEYTYPE                                 //CURLOPT_SSLKEY中规定的私钥的加密类型，支持的密钥类型为"PEM"(默认值)、"DER"和"ENG"。
CURLOPT_UNIX_SOCKET_PATH                           //使用 Unix 套接字作为连接，并用指定的 string 作为路径。 cURL 7.40.0 中添加， PHP 7.0.7 起有效。
CURLOPT_URL                                        //需要获取的 URL 地址，也可以在curl_init() 初始化会话的时候。
CURLOPT_USERAGENT                                  //在HTTP请求中包含一个"User-Agent: "头的字符串。
CURLOPT_USERNAME                                   //验证中使用的用户名。cURL 7.19.1 中添加，PHP 5.5.0 起有效。
CURLOPT_USERPWD                                    //传递一个连接中需要的用户名和密码，格式为："[username]:[password]"。
CURLOPT_XOAUTH2_BEARER                             //指定 OAuth 2.0 access token。 cURL 7.33.0 中添加，自 PHP 7.0.7 添加。
// array options
CURLOPT_CONNECT_TO                                 //连接到指定的主机和端口，替换 URL 中的主机和端口。接受指定字符串格式的数组： HOST:PORT:CONNECT-TO-HOST:CONNECT-TO-PORT。    cURL 7.49.0 中添加， PHP 7.0.7 起有效。
CURLOPT_HTTP200ALIASES                             //HTTP 200 响应码数组，数组中的响应码被认为是正确的响应，而非错误。在 cURL 7.10.3 中被加入。
CURLOPT_HTTPHEADER                                 //设置 HTTP 头字段的数组。格式： array('Content-type: text/plain', 'Content-length: 100')
CURLOPT_POSTQUOTE                                  //在 FTP 请求执行完成后，在服务器上执行的一组array格式的 FTP 命令。
CURLOPT_PROXYHEADER                                //传给代理的自定义 HTTP 头。   cURL 7.37.0 中添加，自 PHP 7.0.7 添加。
CURLOPT_QUOTE                                      //一组先于 FTP 请求的在服务器上执行的FTP命令。
CURLOPT_RESOLVE                                    //提供自定义地址，指定了主机和端口。 包含主机、端口和 ip 地址的字符串，组成 array 的，每个元素以冒号分隔。格式： array("example.com:80:127.0.0.1")    在 cURL 7.21.3 中添加，自 PHP 5.5.0 起可用。
// stream resource options
CURLOPT_FILE                                       //设置输出文件，默认为STDOUT (浏览器)。
CURLOPT_INFILE                                     //上传文件时需要读取的文件。
CURLOPT_STDERR                                     //错误输出的地址，取代默认的STDERR。
CURLOPT_WRITEHEADER                                //设置 header 部分内容的写入的文件地址。
// function or callback options
CURLOPT_HEADERFUNCTION                             //设置一个回调函数，这个函数有两个参数，第一个是cURL的资源句柄，第二个是输出的 header 数据。header数据的输出必须依赖这个函数，返回已写入的数据大小。
CURLOPT_PASSWDFUNCTION                             //设置一个回调函数，有三个参数，第一个是cURL的资源句柄，第二个是一个密码提示符，第三个参数是密码长度允许的最大值。返回密码的值。
CURLOPT_PROGRESSFUNCTION                           //设置一个回调函数，有五个参数，第一个是cURL的资源句柄，第二个是预计要下载的总字节（bytes）数。第三个是目前下载的字节数，第四个是预计传输中总上传字节数，第五个是目前上传的字节数。Note:只有设置 CURLOPT_NOPROGRESS 选项为 FALSE 时才会调用这个回调函数。返回非零值将中断传输。 传输将设置 CURLE_ABORTED_BY_CALLBACK 错误。
CURLOPT_READFUNCTION                               //回调函数名。该函数应接受三个参数。第一个是 cURL resource；第二个是通过选项 CURLOPT_INFILE 传给 cURL 的 stream resource；第三个参数是最大可以读取的数据的数量。回 调函数必须返回一个字符串，长度小于或等于请求的数据量（第三个参数）。一般从传入的 stream resource 读取。返回空字符串作为 EOF（文件结束） 信号。
CURLOPT_WRITEFUNCTION                              //回调函数名。该函数应接受两个参数。第一个是 cURL resource；第二个是要写入的数据字符串。数 据必须在函数中被保存。 函数必须准确返回写入数据的字节数，否则传输会被一个错误所中 断。
*/
//一个完整的http请求数据示例：
//GET /sapp/designhjy/ HTTP/1.1
//Host: shouji.sogou.com
//Cache-Control: max-age=0
//Upgrade-Insecure-Requests: 1
//User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36
//Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8
//Accept-Encoding: gzip, deflate
//Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,fr;q=0.6,de;q=0.5
//If-None-Match: "5b595cc9-42ee"
//If-Modified-Since: Thu, 26 Jul 2018 05:31:53 GMT
//
//
/*
一个完整的http响应数据示例：
HTTP/1.1 200 OK
Server: nginx
Date: Thu, 30 Aug 2018 17:04:32 GMT
Content-Type: text/html;charset=utf-8
X-Powered-By: PHP/5.5.38
Set-Cookie: PHPSESSID=hn5n383dp18vdvtg6ha6fj5lq5; path=/
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
Pragma: no-cache
Set-Cookie: auth=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/; domain=.shouji.sogou.com
Set-Cookie: uid=0; path=/; domain=.shouji.sogou.com
Set-Cookie: nickname=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/; domain=.shouji.sogou.com
Set-Cookie: storage=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/; domain=.shouji.sogou.com
Transfer-Encoding: chunked
Proxy-Connection: Keep-alive
{"uid":0,"nickname":"","headimgurl":""}
*/
class curlUtil
{
    private static $instance = null;
    protected static $options = array();
    protected static $defaultOpts = array(
        'method' => 'get',
        'cookie' => false,
        'headers' => array(),
        'curlopts' => array(
            CURLOPT_FOLLOWLOCATION => true,         //TRUE 时将会根据服务器返回 HTTP 头中的 "Location: " 重定向。
            CURLOPT_MAXREDIRS => 256,               //指定最多的 HTTP 重定向次数，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的。
            CURLOPT_CONNECTTIMEOUT => 3,            //发起连接前等待的时间
            CURLOPT_RETURNTRANSFER => true,         //将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
            CURLOPT_AUTOREFERER => true,            //根据Location:重定向时，自动设置header中的Referer:信息。
            CURLOPT_TIMEOUT => 3,                   //允许 cURL 函数执行的最长秒数。
            CURLOPT_SSL_VERIFYPEER => false,        //https请求 不验证证书和hosts FALSE 禁止 cURL 验证对等证书（peer's certificate）。要验证的交换证书可以在 CURLOPT_CAINFO 选项中设置，或在 CURLOPT_CAPATH中设置证书目录。自cURL 7.10开始默认为 TRUE。从 cURL 7.10开始默认绑定安装。
            CURLOPT_SSL_VERIFYHOST => 0,            //https请求 不验证证书和hosts
            CURLOPT_USERAGENT => 'icurl-1.0.0',
        ),
        'parseRespHead' => true,                    //是否解析响应头
        'getHeader' => true,                        //是否获取http的响应头
    );
    protected static $fixedOpts = array(
        CURLOPT_URL,
        CURLOPT_HTTPGET,
        CURLOPT_POST,
        CURLOPT_CUSTOMREQUEST,
        CURLOPT_HEADER,
        CURLOPT_COOKIEFILE,
        CURLOPT_COOKIEJAR,
    );
    public function __construct($options = array())
    {
          self::setOptions($options);
    }
    private function __clone()
    {
    }
    public function __destruct()
    {
    }
    public static function getInstance($options = array())
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($options);
        }
        return self::$instance;
    }
    /**
     *  设置选项
     *  @param $options array
     *      array(
     *          'method' => String, // get/post/put/head/delete
     *          'cookie' => Boolean(false)/String, // false表示不启用cookie, 或者填cookie文件的绝对路径,
     *          'headers' => Array(), // 一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置： array('Content-type: text/plain', 'Content-length: 100')
     *          'curlopts' => array(), // curl的 options 选项数组 如 array(CURLOPT_RETURNTRANSFER => true, ...)
     *          'parseRespHead' => Boolean,
     *          'getHeader' => Boolean,
     *      )
     * @return Array
     */
    private static function setOptions($options = array())
    {
        if (empty(self::$options)) {
            self::$options = self::$defaultOpts;
        }
        // 数字下标的array不能merge，否则下标会从0开始计
        $defaultCurlopts = self::$options['curlopts'];
        $curlopts = isset($options['curlopts']) ? $options['curlopts'] : array();
        foreach ($curlopts as $key => $value) {
            if (!in_array($key, self::$fixedOpts, true)) {
                $defaultCurlopts[$key] = $value;
            }
        }
        if (!isset($curlopts[CURLOPT_USERAGENT]) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $defaultCurlopts[CURLOPT_USERAGENT] = $_SERVER['HTTP_USER_AGENT'];
        }
        self::$options = array_merge(self::$options, $options); // overwrite default options
        self::$options['curlopts'] = $defaultCurlopts;
        return self::$options;
    }
    /**
     *  设置curl的opt
     *  @param $ch Resource curl_init() 的句柄
     *  @param $url String 请求的url地址
     *  @param $args Array/String get或post或其他请求的参数
     *  @param $opts Array 选项 参看 setOptions 方法的参数值结构
     *      array(
     *          'method' => String, // get/post/put/head/delete
     *          'cookie' => Boolean(false)/String, // false表示不启用cookie, 或者填cookie文件的绝对路径,
     *          'headers' => Array(), // 一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置： array('Content-type: text/plain', 'Content-length: 100')
     *          'curlopts' => array(), // curl的 options 选项数组 如 array(CURLOPT_RETURNTRANSFER => true, ...)
     *          'parseRespHead' => Boolean,
     *          'getHeader' => Boolean,
     *      )
     *  @return Array
     */
    protected static function curlSetopt($ch, $url, $args, $opts) {
        $opts = self::setOptions($opts);
        $curl_options = isset($opts['curlopts']) ? $opts['curlopts'] : array();
        $opts['method'] = strtoupper($opts['method']);
        if ($opts['method'] == 'GET') {
            $url = self::appendUrlArgs($url, $args);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($curl_options) {
            curl_setopt_array($ch, $curl_options);
        }
        if ($opts['getHeader']) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        } else {
            curl_setopt($ch, CURLOPT_HEADER, false);
        }
        if(!empty($opts['headers'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $opts['headers']);
        }
        if ($opts['method'] == 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } elseif ($opts['method'] == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, (is_string($args) ? $args : http_build_query($args)));
        } elseif ($opts['method'] == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        } elseif ($opts['method'] == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } elseif ($opts['method'] == 'HEAD') {
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
            curl_setopt($ch, CURLOPT_NOBODY, true);
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        if ($opts['cookie'] !== false) {
            if (file_exists($opts['cookie'])) {
                curl_setopt($ch, CURLOPT_COOKIEFILE, $opts['cookie']); //附加cookies
            }
            curl_setopt($ch, CURLOPT_COOKIEJAR, $opts['cookie']);//存储cookies
        }
        return $opts;
    }
    /**
     *  http get 方法
     *  @param $url String
     *  @param $opts Array 选项 参看 setOptions 方法的参数值结构
     *  @return Array
     *     array(
     *          'code' => 0, //Integer
     *          'msg' => 'succ', //String
     *          'data' => array(
     *              'response' => array(
     *                  'headLine' => $headLine,
     *                  'headArr' => $headArr,
     *                  'cookie' => $cookies,
     *                  'head' => $head,
     *                  'body' => $body
     *               ),
     *              'http_code' => 0,
     *              'curl_errno' => 0,
     *              'curl_error' => '',
     *              'curl_info' => array(),
     *          ),
     *      )
    */
    public static function get($url, $opts = array())
    {
        $opts['method'] = 'GET';
        $resu = self::request($url, array(), $opts);
        if ($resu['code'] != 0) {
            $resu['data']['response'] = array();
            return $resu;
        }
        $resu['data']['response'] = self::parseResponse($resu['data']['response']);
        return $resu;
    }
        /**
     *  http post 方法
     *  @param $url String
     *  @param $postArgs Array/String
     *  @param $opts Array 选项 参看 setOptions 方法的参数值结构
     *  @return Array 参看 get 的返回值
    */
    public static function post($url, $postArgs = array(), $opts = array())
    {
        $opts['method'] = 'POST';
        $resu = self::request($url, $postArgs, $opts);
        if ($resu['code'] != 0) {
            $resu['data']['response'] = array();
             return $resu;
        }
        $resu['data']['response'] = self::parseResponse($resu['data']['response']);
        return $resu;
    }
        /**
     *  http head 方法
     *  @param $url String
     *  @param $opts Array 选项 参看 setOptions 方法的参数值结构
     *  @return Array 参看 get 的返回值
    */
    public static function head($url, $opts = array())
    {
        $opts['method'] = 'HEAD';
        $resu = self::request($url, array(), $opts);
        if ($resu['code'] != 0) {
            $resu['data']['response'] = array();
            return $resu;
        }
        $resu['data']['response'] = self::parseResponse($resu['data']['response']);
        return $resu;
    }
        /**
     *  http put 方法
     *  @param $url String
     *  @param $opts Array 选项 参看 setOptions 方法的参数值结构
     *  @return Array 参看 get 的返回值
    */
    public static function put($url, $opts = array())
    {
        $opts['method'] = 'PUT';
        $resu = self::request($url, array(), $opts);
        if ($resu['code'] != 0) {
            $resu['data']['response'] = array();
            return $resu;
        }
        $resu['data']['response'] = self::parseResponse($resu['data']['response']);
        return $resu;
    }
        /**
     *  http delete 方法
     *  @param $url String
     *  @param $opts Array 选项 参看 setOptions 方法的参数值结构
     *  @return Array 参看 get 的返回值
    */
    public static function delete($url, $opts = array())
    {
        $opts['method'] = 'DELETE';
        $resu = self::request($url, array(), $opts);
        if ($resu['code'] != 0) {
            $resu['data']['response'] = array();
            return $resu;
        }
        $resu['data']['response'] = self::parseResponse($resu['data']['response']);
        return $resu;
    }
    /**
     *  执行curl模拟的http请求
     *  @param $url String 请求的url地址
     *  @param $args String/Array 请求的参数 可以是get参数或post参数
     *  @param $opts Array 设置的选项参数
     *  @return  array
     *      array(
     *          'code' => 0, //Integer
     *          'msg' => 'succ', //String
     *          'data' => array(
     *              'response' => array(), // 有可能只有body数据, 有可能会有完整的响应数据, 视 curl_opts 的 CURLOPT_HEADER 参数而定
     *              'http_code' => 0,
     *              'curl_errno' => 0,
     *              'curl_error' => '',
     *              'curl_info' => array(),
     *          ),
     *      )
     */
    private static function request($url, $args, $opts = array())
    {
        $resu = array(
            'code' => 0,
            'msg' => 'succ',
            'data' => array(
                'response' => array(),
                'http_code' => 0,
                'curl_errno' => 0,
                'curl_error' => '',
                'curl_info' => array(),
            ),
        );
        $ch = curl_init();
        if ($ch === false) {
            $resu['code'] = 1;
            $resu['msg'] = 'curl_init fail';
            return $resu;
        }
        $opts = self::curlSetopt($ch, $url, $args, $opts);
        if (false === ($resp = curl_exec($ch))) {
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);
            $resu['code'] = 2;
            $resu['msg'] = 'curl_exec fail';
            $resu['data']['curl_errno'] = $curl_errno;
            $resu['data']['curl_error'] = $curl_error;
            return $resu;
        }
        $resu['data']['response'] = $resp;
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        $resu['data']['curl_errno'] = $curl_errno;
        $resu['data']['curl_error'] = $curl_error;
        if (0 !== $curl_errno) {
            curl_close($ch);
            $resu['code'] = 3;
            $resu['msg'] = 'curl_exec error';
            return $resu;
        }
        $curl_info = curl_getinfo($ch);
        $resu['data']['http_code'] = $curl_info['http_code'];
        $resu['data']['curl_info'] = $curl_info;
        curl_close($ch);
        return $resu;
    }
    /**
     *  解析 request 方法的返回结果中的 array['data']['response']
     *  @param $response String 完整的http响应文本数据
     *  @return Array
     *        array(
     *            'headLine' => array('v1', 'v2'), //不带key
     *            'headArr' => array('k1' => 'v1', 'k2' => 'v2', ...), //key=>value结构
     *            'cookie' => array(),
     *            'head' => '', //文本形式的请求头 = headLine + headArr
     *            'body' => '',
     *        )
     */
    private static function parseResponse($response)
    {
        if (self::$options['getHeader']) {
            $head = self::getResponseHeader($response);
            if (strlen($head) == strlen($response)) {//无body数据(有可能是重定向)
                $body = '';
            } else {
                $body = substr($response, strlen($head)+4);
            }
        } else {
              $head = '';
              $body = $response;
        }
        $headLine = $headArr = $cookies = array();
        if (self::$options['getHeader'] && self::$options['parseRespHead']) {
            $headerLines = explode("\r\n", $head);
            foreach ($headerLines as $line) {
                $line = trim($line);
                if (strlen($line) > 0) {
                    if (false !== strpos($line, ':')) {
                        list($k,$v) = explode(":", $line);
                        $k = trim($k);
                        $v = trim($v);
                        if ($k == 'Set-Cookie') {
                            list($ck, $cv) = explode("=", $v);
                            $cookies[trim($ck)] = trim($cv);
                        } else {
                            $headArr[$k] = $v;
                        }
                    } else {
                        $headLine[] = $line;
                    }
                }
            }
        }
        return array(
            'headLine' => $headLine,
            'headArr' => $headArr,
            'cookie' => $cookies,
            'head' => $head,
            'body' => $body
        );
    }
    /**
     *  获取http响应数据的响应头文本信息 多次重定向会包含多个响应头 这里获取全部响应头
     *  @param $response String 完整的http响应文本数据（包含 状态行, 响应head, 响应body）
     *  @param $pos 截取的起始偏移量(从0开始计算的偏移量值)
     *  @return String
     */
    protected static function getResponseHeader($response, $pos = 0)
    {
        //if (false !== stripos($response, "\r\n\r\nHTTP/1.1 ", $pos)) {//表明有多个响应状态行
        if (false !== ($tmpPos = stripos($response, "\r\n\r\nHTTP/", $pos))) {//表明可能有多个响应状态行
            if (false !== ($nextCrlfPos = strpos($response, "\r\n", $tmpPos+4))) {
                $line = substr($response, $tmpPos+4, $nextCrlfPos-($tmpPos+4));
                // HTTP/1.1 302 Found
                // HTTP/1.1 302 Moved Temporarily
                //if (preg_match('/^http\/[\d\.]+ \d+ \S+$/i', $line)) {
                if (preg_match('/^http\/[\d\.]+\s+\d+\s+[\s\S]+$/i', $line)) {
                    return self::getResponseHeader($response, $tmpPos+4);
                }
            }
        }
        if (false === ($pos = strpos($response, "\r\n\r\n", $pos))) {
            return $response;
        }
        return substr($response, 0, $pos);
    }
    /**
     * [appendUrlArgs 追加url的get参数]
     * @param  String $url
     * @param  Array/String $args 缺省arrar() eg. 'a=b&c=d' or array('a'=>b,'c'=>'d',..)
     * @return String
     */
    public static function appendUrlArgs($url, $args = array())
    {
        if (is_array($args)) {
            $args = http_build_query($args);
        }
        if (strlen($args) < 1) {
            return $url;
        }
        return ($url .= ( false === strpos($url, '?') ? '?' : '&' ) . $args);
    }

    /**
     * curl_multi方法 参考资料:https://www.cnblogs.com/52fhy/p/8908315.html
     * @param  Array $configs
     *     array(
     *         0 => array(
     *             'url' => '',
     *             'args' => array(), // post 或 get 的参数 缺省arrar() eg. 'a=b&c=d' or array('a'=>b,'c'=>'d',..)
     *             'opts' => array(), // Array 设置的选项参数 参看 setOptions 方法的参数值结构
     *         ),
     *        ...
     *     )
     * @return Array
     *     array(
     *         0 => array(
     *             'code' => 0,
     *             'msg' => 'succ',
     *             'data' => array(
     *                 'response' => array(),
     *                 'http_code' => 200,
     *                 'curl_errno' => 0,
     *                 'curl_error' => '',
     *                 'curl_info' => array(
     *                     'url' => '',
     *                     'content_type' => '',
     *                     'http_code' => 200,
     *                     ...
     *                 ),
     *             ),
     *         ),
     *         ...
     *     )
     */
    public function multi($configs) {

        $resu = $chArr = $opts = array();

        foreach ($configs as $k => $cfg) {
            if (false !== ($chArr[$k] = curl_init())) {
                $opts[$k] = self::curlSetopt($chArr[$k], $cfg['url'], $cfg['args'], $cfg['opts']);
            }
            $resu[$k] = array(
                'code' => 0,
                'msg' => 'succ',
                'data' => array(
                    'response' => array(),
                    'http_code' => 0,
                    'curl_errno' => 0,
                    'curl_error' => '',
                    'curl_info' => array(),
                ),
            );
        }

        $mh = curl_multi_init(); // 1 创建批处理cURL句柄

        foreach ($chArr as $k => $ch) {
            if ($ch !== false) {
                curl_multi_add_handle($mh, $ch); // 2 增加句柄
            }
        }

        $active = null;
        do {
            while (($mrc = curl_multi_exec($mh, $active)) == CURLM_CALL_MULTI_PERFORM);

            if ($mrc != CURLM_OK) {
                break;
            }

            // a request was just completed -- find out which one
            while ($done = curl_multi_info_read($mh)) {
                $idx = null;
                foreach ($chArr as $k => $ch) {
                    if ($done['handle'] === $ch) {
                        $idx = $k;
                        break;
                    }
                }
                // get the info and content returned on the request
                $curl_errno = curl_errno($done['handle']);
                $curl_error = curl_error($done['handle']);
                $resu[$idx]['data']['curl_errno'] = $curl_errno;
                $resu[$idx]['data']['curl_error'] = $curl_error;
                if (0 !== $curl_errno) {
                    $resu[$idx]['code'] = 3;
                    $resu[$idx]['msg'] = 'curl_exec error';
                    $resu[$idx]['data']['response'] = array();
                } else {
                    $curl_info = curl_getinfo($done['handle']);
                    $resu[$idx]['data']['http_code'] = $curl_info['http_code'];
                    $resu[$idx]['data']['curl_info'] = $curl_info;
                    $response = curl_multi_getcontent($done['handle']);
                    $resu[$idx]['data']['response'] = self::parseResponse($response);
                }
                // remove the curl handle that just completed
                curl_multi_remove_handle($mh, $done['handle']);
                curl_close($done['handle']);
                unset($chArr[$idx]);
            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($active > 0) {
                curl_multi_select($mh);
            }

        } while ($active);
        curl_multi_close($mh); // close all handle

        if (count($chArr) > 0) { // curl_init 失败的在这里
            foreach ($chArr as $k=> $ch) {
                $resu[$k] = array_replace_recursive($resu[$k], array(
                    'code' => 1,
                    'msg' => 'curl_init fail',
                ));
            }
        }

        return $resu;
    }
}
