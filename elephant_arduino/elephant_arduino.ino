#include "DHT.h"
#include <SoftwareSerial.h>  

#define PUMP 3
#define MOISTURE A0
#define RAIN A1
#define DHTPIN 7
#define WIFI 5

#define DHTTYPE DHT11


int rxPin = 10;                                              
int txPin = 11;

String channelID = "1759852";
String ip = "184.106.153.149";                                //Thingspeak ip address
                     
String wifiName = "MiTelefon";                
String wifiPassword = "00000001";
String apiKey = "4UAQEXQT2MXZ0H3K";


SoftwareSerial esp(rxPin, txPin);
DHT dht(DHTPIN, DHTTYPE);

float humidityData;
float temperatureData;
float moistureData;
float rainData;
int irrigate = 0;
int oldState = 0;
int raining = 0;

float max_moisture = 800;
float min_moisture = 200;
float rainLevel = 500;

void setup() {
  dht.begin();
  pinMode(MOISTURE, INPUT);
  pinMode(WIFI, OUTPUT);
  pinMode(RAIN, INPUT);
  pinMode(PUMP, OUTPUT);
  Serial.begin(9600);

  esp.begin(115200);                                          
  esp.println("AT");                                     
  Serial.println("AT Sent");
  while (!esp.find("OK")) {                                   
    esp.println("AT");
    Serial.println("Could not find ESP8266.");
  }
  Serial.println("OK command taken");
  esp.println("AT+CWMODE=1");                             
  while (!esp.find("OK")) {                                 
    esp.println("AT+CWMODE=1");
    Serial.println("Performing settings...");
  }
  Serial.println("Set as Client");
  Serial.println("Connecting to WIFI...");
  esp.println("AT+CWJAP=\"" + wifiName + "\",\"" + wifiPassword + "\""); 
  while (!esp.find("OK"));                              
  Serial.println("Connected to WIFI");
  delay(500);
}

void loop() {
  esp.println("AT+CIPSTART=\"TCP\",\"" + ip + "\",80");       //Thingspeak connection
  if (esp.find("Error"))
  { 
    Serial.println("AT+CIPSTART Error");
  }

  moistureData = analogRead(MOISTURE);
  rainData = analogRead(RAIN);
  humidityData = dht.readHumidity();
  temperatureData = dht.readTemperature();

  checkMoisture(moistureData);
  checkRain(rainData);
  if (irrigate != oldState) {
    if (irrigate) {
      digitalWrite(PUMP, LOW);
    } else {
      digitalWrite(PUMP, HIGH);
    }
    oldState = irrigate;
  }

  String data = "GET https://api.thingspeak.com/update?api_key=" + apiKey;   
  data += "&field1=";
  data += String(temperatureData);
  data += "&field2=";
  data += String(humidityData);
  data += "&field3=";
  data += String(moistureData);
  data += "&field4=";
  data += String(raining);
  data += "&field5=";
  data += String(irrigate);

  data += "\r\n\r\n";

  esp.print("AT+CIPSEND=");                                  
  esp.println(data.length() + 2);
  //Serial.println(esp.find("&gt;"));

  //Serial.print(",");
  Serial.print(moistureData);
  Serial.print(",");
  Serial.print(humidityData);
  Serial.print(",");
  Serial.print(temperatureData);
  Serial.print(",");
  Serial.print(raining);
  Serial.print(",");
  Serial.println(irrigate);

  delay(2000);

  esp.print(data);
  //Serial.print("Response: ");

  //Serial.println(esp.readString());

  //Serial.println(data)
  
  Serial.println("Data sent.");
  
  digitalWrite(WIFI, HIGH);
  delay(1000);
  digitalWrite(WIFI, LOW);

  Serial.println("Connection closed.");
  esp.println("AT+CIPCLOSE");                              
  delay(1000);
}

void checkRain(float data) {
  if (data > rainLevel) {
    irrigate = 0;
    raining = 1;
  } else if (data < rainLevel - 200) {
    raining = 0;
  }
}

void checkMoisture(float data) {
  data = abs(data - 1000);
  if (data < min_moisture) {
    irrigate = 1;
  } else if (data > max_moisture) {
    irrigate = 0;
  }
}
