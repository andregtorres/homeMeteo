#include <Wire.h>
#include "CFF_ChipCap2.h"

CFF_ChipCap2 cc2 = CFF_ChipCap2();

void setup() {
  Serial.begin(115200); 
  cc2.begin();
  while(!Serial){} // Waiting for serial connection
  Serial.println("Start I2C reader ...");
  cc2.startNormalMode();
  delay(100);
  }

void loop() {
  if (cc2.dataReady() == true)
    {
      cc2.readSensor();
      
      Serial.print("Humidity: ");
      Serial.print(cc2.humidity);
      Serial.print("\n");
  
      Serial.print("Temperature C: ");
      Serial.print(cc2.temperatureC);
      Serial.print("\n");

      
    }
 delay(5000);
}
