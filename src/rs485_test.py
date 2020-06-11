from ctypes import *
import serial


def get_modbus_crc(int_array):
    value = 0xFFFF
    for i in range(len(int_array)):
        value ^= int_array[i]
        for j in range(8):
            if (value & 0x01) == 1:
                value = (value >> 1) ^ 0xA001
            else:
                value >>= 1
    return [value & 0xff, (value >> 8) & 0xff]

tr_pin = 6
wiringpi = cdll.LoadLibrary('libwiringPi.so')
wiringpi.wiringPiSetupGpio()
wiringpi.pinMode(tr_pin, 1)
wiringpi.digitalWrite(tr_pin, 0)

ser_m = serial.Serial(port="/dev/ttyS1", baudrate=2400, timeout=1, write_timeout=1)
if not ser_m.isOpen():
    ser_m.open()

wiringpi.digitalWrite(tr_pin, 1)

msg = [11, 3, 0, 0, 0, 2]
crc = get_modbus_crc(msg)
ser_m.write(msg + crc)
ser_m.flush()

wiringpi.digitalWrite(tr_pin, 0)

r_data = ser_m.read(256)
hex_data = r_data.hex()
data_list = [int(hex_data[i:i + 2], 16) for i in range(0, len(hex_data), 2)]
print(data_list)
