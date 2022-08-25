## A. PREPARACION DE LA MÁQUINA

1. Actualización del sistema

   ```bash
   root@antonio:~# apt update
   root@antonio:~# apt upgrade
   ```

2. Instalación de software esencial

```bash
apt install -y build-essential fail2ban iptables-persistent msmtp-mta python3-dev python3-pip libcurl4-openssl-dev libssl-dev htop git neovim wget curl tmux
apt autoremove -y
```

3. Creamos un fichero de 1GB como medida de seguridad ante el disco lleno. En caso de que el disco se llene siempre podemos borrar el fichero y trabajar con este espacio.

```bash
dd if=/dev/urandom of=balloon.txt bs=1MB count=1000
```

4. Instalación de file2ban 

```bash
root@antonio:/home# sudo apt install file2ban
root@antonio:/home# sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
```

5. Creación de nuevo usuario no root.

```bash
root@antonio:/home# sudo useradd -m -s /bin/bash antonio
root@antonio:/home/antonio# sudo passwd antonio
```

6. Agregamos usuario a sudoers.

```bash
root@antonio:~# usermod -aG sudo antonio
root@antonio:~# groups antonio
antonio : antonio sudo
```

7. Generación de tiempo local por error al iniciar.

```bash
*** System restart required ***
Last login: Mon Aug 22 18:51:33 2022 from 81.169.218.215
-bash: warning: setlocale: LC_ALL: cannot change locale (de_DE.utf8)

root@antonio:~# dpkg-reconfigure locales
Generating locales (this might take a while)...
  de_DE.UTF-8... done
  en_US.UTF-8... done
Generation complete.
```

8. Entramos a la configuración de ssh para impedir el acceso por ssh a través de root para aumentar la seguridad.

```bash
antonio@antonio:~$ sudo vim /etc/ssh/sshd_config

vim..
PermitRootLogin no

antonio@antonio:~$ sudo systemctl reload ssh
```

9. Configuramos las reglas para iptables para el puerto, el puerto 80 y el puerto 443

   ```bash
   antonio@antonio:~$ sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
   antonio@antonio:~$ sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
   antonio@antonio:~$ sudo iptables -L
   Chain INPUT (policy ACCEPT)
   target     prot opt source               destination
   f2b-sshd   tcp  --  anywhere             anywhere             multiport dports ssh
   ACCEPT     tcp  --  anywhere             anywhere             tcp dpt:http
   ACCEPT     tcp  --  anywhere             anywhere             tcp dpt:https
   
   Chain FORWARD (policy ACCEPT)
   target     prot opt source               destination
   
   Chain OUTPUT (policy ACCEPT)
   target     prot opt source               destination
   
   Chain f2b-sshd (1 references)
   target     prot opt source               destination
   REJECT     all  --  45.240.88.20         anywhere             reject-with icmp-port-unreachable
   RETURN     all  --  anywhere             anywhere
   
   ```

   

## B. Otras configuraciones

1. Configuramos Git. El cual ya está instalado pero debemos configurar.

   ```bash
   root@antonio:~# git --version
   git version 2.25.1
   
   root@antonio:~# git config --global user.name "Antonio"
   root@antonio:~# git config --global user.email "autonomoadame@gmail.com"
   root@antonio:~# git config --global core.editor "vim"
   
   root@antonio:~# git config --list
   user.name=Antonio
   user.email=autonomoadame@gmail.com
   ```



## C. Aplicaciones.

#### 	1.  Nginx

- Instalación 

  ```bash
  antonio@antonio:~$ sudo apt install nginx
  antonio@antonio:~$ sudo systemctl stop nginx.service
  antonio@antonio:~$ sudo systemctl start nginx.service
  antonio@antonio:~$ sudo systemctl enable nginx.service
  Synchronizing state of nginx.service with SysV service script with /lib/systemd/systemd-sysv-install.
  Executing: /lib/systemd/systemd-sysv-install enable nginx
  antonio@antonio:~$ sudo systemctl status nginx.service
  ● nginx.service - A high performance web server and a reverse proxy server
       Loaded: loaded (/lib/systemd/system/nginx.service; enabled; vendor preset: enabled)
       Active: active (running) since Thu 2022-08-25 09:23:51 UTC; 12s ago
         Docs: man:nginx(8)
     Main PID: 60318 (nginx)
        Tasks: 3 (limit: 4556)
       Memory: 3.4M
       CGroup: /system.slice/nginx.service
               ├─60318 nginx: master process /usr/sbin/nginx -g daemon on; master_process on;
               ├─60319 nginx: worker process
               └─60320 nginx: worker process
  
  Aug 25 09:23:51 antonio systemd[1]: Starting A high performance web server and a reverse proxy server...
  Aug 25 09:23:51 antonio systemd[1]: Started A high performance web server and a reverse proxy server.
  ```

- Comprobación

![](/home/siverio/Documentos/typoraImages/image-20220825112940971.png)

- Ruta de Nginx y cambio de permisos. Después añadimos el usuario al grupo www-data.

  ```bash
  antonio@antonio:/usr/share/nginx$ sudo chown -R www-data:www-data html/
  
  antonio@antonio:/usr/share/nginx$ sudo usermod -aG www-data antonio
  antonio@antonio:/usr/share/nginx$ groups antonio
  antonio : antonio sudo www-data
  antonio@antonio:/usr/share/nginx$ sudo chmod 775 -R  html/
  antonio@antonio:/usr/share/nginx$ ls -salh
  total 16K
  4.0K drwxr-xr-x   4 root     root     4.0K Aug 25 09:23 .
  4.0K drwxr-xr-x 148 root     root     4.0K Aug 25 10:08 ..
  4.0K drwxrwxr-x   2 www-data www-data 4.0K Aug 25 17:48 html
     0 lrwxrwxrwx   1 root     root       23 Apr 12 08:04 modules -> ../../lib/nginx/modules
  4.0K drwxr-xr-x   2 root     root     4.0K Aug 25 09:23 modules-available
  ```

- Tras ello iniciamos el proyecto .git en la carpeta principal de /usr/nginx/html de nginx

  ```bash
  antonio@antonio:/usr/share/nginx/html$ sudo git init
  Initialized empty Git repository in /usr/share/nginx/html/.git/
  ```

#### 2. Postgresql 

- Instalación del paquete principal y "-contrib", que otorga funcionalidades adicionales

```bash
sudo apt-get install postgresql postgresql-contrib	
```

- PostgreSQL usa cuentas de usuario adicionales para determinar los roles de usuario. Para acceder a la información de las bases de datos se puede cambiar a la cuenta postgres y luego acceder a la administración de la base de datos con psql.

  ```bash
  antonio@antonio:~$ psql
  psql: error: FATAL:  role "antonio" does not exist
  antonio@antonio:~$ sudo -iu postgres
  postgres@antonio:~$ psql
  psql (12.12 (Ubuntu 12.12-0ubuntu0.20.04.1))
  Type "help" for help.
  ```

- Una alternativa sería usar de forma directa el comando con la cuenta de usuario

  ```
  sudo -u postgres psql
  ```

- Aprovecharemos la instalación de Postgresql para la creación de la base de datos de Moodle y su usuario.

  ```bash
  postgres=# CREATE USER moodle WITH PASSWORD '*************';
  CREATE ROLE
  postgres=# CREATE DATABASE moodle;
  CREATE DATABASE
  postgres=# GRANT ALL PRIVILEGES ON DATABASE moodle to moodle;
  GRANT
  postgres=# \q
  ```

#### 3. PHP y dependencias de Moodle.

- Instalamos todas las dependencias, módulos y demás programas que necesita moodle para funcionar.

```bash
sudo apt install graphviz aspell ghostscript clamav php7.4-pspell php7.4-curl php7.4-gd php7.4-intl php7.4-mysql php7.4-xml php7.4-xmlrpc php7.4-ldap php7.4-zip php7.4-soap php7.4-mbstring libmagic-mgc libmagic1 php7.4-cli php7.4-fpm php7.4-json php7.4-opcache php7.4-pgsql php7.4-readline
```

