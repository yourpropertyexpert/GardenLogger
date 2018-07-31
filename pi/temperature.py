
import os
import glob
import time

os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')

base_dir = '/sys/bus/w1/devices/'


 
device_count = len(glob.glob(base_dir + '28*'))




def read_temp_raw(sensor):
  device_folder = glob.glob(base_dir + '28*')[sensor]
  device_file = (device_folder + '/w1_slave')
  f = open(device_file, 'r')
  lines = f.readlines() 
  f.close()
  return lines

def read_temp(sensor):
  lines = read_temp_raw(sensor)
  
  while lines[0].strip()[-3:] != 'YES':
    time.sleep(0.2)
    lines = read_temp_raw(sensor)

  equals_pos = lines[1].find('t=')

  if equals_pos != -1:
    temp_string = lines[1][equals_pos+2:]
    temp_c = float(temp_string) / 1000.0
    return temp_c

def output_temp(sensor):
  print ('sensor')
  print (sensor)
  print ('sensor code')
  device_folder = glob.glob(base_dir + '28*')[sensor]
  device_name = device_folder[device_folder.rfind("/")+1:]
  print (device_name)
  print ('temperature')
  print (read_temp(sensor))


x = 0

while True:
  while x < device_count:
    output_temp(x)
    x +=1
 
  x = 0

  time.sleep(30)

