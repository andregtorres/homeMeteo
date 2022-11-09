#include <WiFiClient.h>
#include <ESP8266WebServer.h>
#include <ESP8266HTTPClient.h>
#include "CFF_ChipCap2.h"

//DEFINITIONS
#define ssid "NETWORK_ID_GOES_HERE"
#define password "WIFI_PW_GOES_HERE"
#define srv_adress "http://web.tecnico.ulisboa.pt/andregtorres/homeMeteo/postData.php"
#define session_id 99 //change number acordingly

//initializations
int i =0;
unsigned long lastTime = 0;
unsigned long timerDelay = 10000; // Set timer to 10 seconds
unsigned short id = session_id;
CFF_ChipCap2 cc2 = CFF_ChipCap2();

//function to connect to wifi
void connectWifi(){
  WiFi.mode(WIFI_OFF);        //Prevents reconnection issue (taking too long to connect)
  delay(100);
  WiFi.mode(WIFI_STA);        //This line hides the viewing of ESP as wifi hotspot
  WiFi.begin(ssid, password);     //Connect to your WiFi router
  Serial.println("");
  Serial.print("Connecting");
  // Wait for connection
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  //If connection successful show IP address in serial monitor
  Serial.print("Connected to ");
  Serial.println(ssid);
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());  //IP address assigned to your ESP
}

//function to send sensor data
void pushData(unsigned int &temp, unsigned int &humi) {
  HTTPClient http;    //Declare object of class HTTPClient

  String postData;
  postData = "id="+String(id)+"&temp="+String(temp)+"&humi="+String(humi);

  http.begin(srv_adress);              //change the ip to your computer ip address
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");    //Specify content-type header

  //int httpCode = http.GET();
  int httpCode = http.POST(postData);   //Send the request
  String payload = http.getString();    //Get the response payload

  Serial.print("http: ");
  Serial.println(httpCode);   //Print HTTP return code
  //Serial.println(payload);    //Print request response payload

  http.end();  //Close connection
}


void readSensor(unsigned int &temp, unsigned int &humi){
    if (cc2.dataReady() == true)
    {
      cc2.readSensor();
      
      Serial.print("Humidity: ");
      Serial.print(cc2.humidity);
      Serial.print("\n");
  
      Serial.print("Temperature C: ");
      Serial.print(cc2.temperatureC);
      Serial.print("\n");
      float temp2, humi2;
      temp2=(cc2.temperatureC)*100;
      humi2=cc2.humidity*100;
      temp=(unsigned int) temp2;
      humi=(unsigned int) humi2;
      //Serial.println(temp);
      //Serial.println(humi);
    }
}

void setup() {
  // put your setup code here, to run once:
  cc2.begin();
  Serial.begin(115200);
  while (!Serial) {
    ; // wait for serial port to connect. Needed for native USB port only
  }
  Serial.printf("\nHomeMeteo client\n");
  Serial.printf("Time between acquisition: %ld s\n", timerDelay/1000);
  connectWifi();
  cc2.startNormalMode();
  delay(100);
}

void loop() {
  // put your main code here, to run repeatedly:
  //timer loop
  if ((millis() - lastTime) > timerDelay) {
    Serial.print("----------\ni= ");
    Serial.println(i);
    unsigned int temp, humi;
    if(WiFi.status() == WL_CONNECTED){
      readSensor(temp, humi);
      pushData(temp, humi);
    }
  else{
    connectWifi();  
    }
  i+=1;
  lastTime = millis();
  }
}
