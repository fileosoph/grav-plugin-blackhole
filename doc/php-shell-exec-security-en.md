# Security information for `shell_exec`

The blackhole plugin uses the php command `shell_exec`.
If you use grav as a live site, it is good to disable some php functions like `shell_exec` for security reasons!
@see https://www.cyberciti.biz/faq/linux-unix-apache-lighttpd-phpini-disable-functions/
On some hostings and php configurations the php function `shell_exec` may already be disabled and thus blackhole will not work.

If static page generation does not work, take a look at your php.ini. Maybe `shell_exec` is disabled.
@see https://www.php.net/manual/en/function.shell-exec.php

Consider adding the PHP function `shell_exec` back into the disable_functions after generating with blackhole.
https://www.php.net/manual/en/ini.core.php#ini.disable-functions