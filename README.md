# GardenLogger
A stupidly simple system to receive temperature readings and show them to a human. 

The "stupidly simple" part is important. This isn't about creating a robust website, so much as about working with my teenagers on a summer project.



To make this work, you will need:

- A server running MySQL (if you're getting started, I recommend the mysql:5.7.22 docker image)
- A server running Apache with PHP (if you're getting start, I recommend the php:7.2.7-apache docker image)
- A raspberry Pi with Raspaian and Python installed. [There's nothing too specific about needing Raspian, and I it should work on most Raspberry Pi linuxes.]
- One or more 1Wire temperature sensors connected to the GPIO ports. [This code assumes you already have them installed and working.]


There are several parts to the codebase:

- /pi : A python script, intended for running on a Raspberry PI (or other device with GPIO) connected to Dallas 1Wire temperature sensors. This simple reads all the temperature sensors and, for each one, calls a URI.
- /machineweb : Some PHP, running out on a webserver, to accept the URI calls from the python script, and write the calls to a MySQL database.
- /site : Some PHP, running out on a webserver, to show humans some (pretty) pages about their garden temperature.
- /sql : The SQL required to create the database structures
