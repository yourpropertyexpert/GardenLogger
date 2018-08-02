# GardenLogger
A stupidly simple system to receive temperature readings and show them to a human.

The "stupidly simple" part is important. This isn't about creating a robust website, so much as about working with my teenagers on a summer project.


There are three parts:

- A python script, intended for running on a Raspberry PI (or other device with GPIO) connected to Dallas 1Wire temperature sensors. This simple reads all the temperature sensors and, for each one, calls a URI.
- Some PHP, running out on a webserver, to accept the URI calls from the python script, and write the calls to a MySQL database and to show humans some (pretty) pages about their garden temperature.
- Code to create the database and the users required on a a MySQL server.
