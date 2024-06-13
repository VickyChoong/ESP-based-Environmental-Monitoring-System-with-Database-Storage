#include <ESP8266WiFi.h>            // Include the ESP8266 WiFi library
#include <DHT.h>                    // Include the DHT sensor library
#include <LiquidCrystal_I2C.h>       // Include the LiquidCrystal I2C library
#include <WiFiClient.h>             // Include the WiFiClient library
#include <ESP8266HTTPClient.h>      // Include the HTTPClient library


const char* ssid = "OPPO Reno4 Pro"; // WiFi network name
const char* password = "12345678"; // WiFi network password

const char* serverName = "http://192.168.190.221/AirQualityMonitoring_PHP/insert_data.php"; // URL of the server to send data
String apiKeyValue = "tPmAT5Ab3j7F9"; // API key value for authentication

#define DHTPIN D3           // Pin connected to the DHT sensor
#define DHTTYPE DHT11       // DHT sensor type
DHT dht(DHTPIN, DHTTYPE);   // Create a DHT object

LiquidCrystal_I2C lcd(0x27, 16, 2);

int gas = A0; // Analog pin connected to the gas sensor

void setup() {
  Serial.begin(115200);       // Start serial communication at 115200 baud
  WiFi.begin(ssid, password); // Connect to WiFi network

  while (WiFi.status() != WL_CONNECTED) {  // Wait for WiFi connection
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }

  Serial.println("Connected to WiFi");
  dht.begin();  // Start DHT sensor

  lcd.init();  // Initialize LCD
  lcd.backlight();  // Turn on backlight
  lcd.setCursor(3, 0);  // Set cursor position
  lcd.print("Air Quality");  // Display title on LCD
  lcd.setCursor(3, 1);
  lcd.print("Monitoring");
  delay(2000);
  lcd.clear();  // Clear LCD screen
}

void loop() {
  float h = dht.readHumidity();   // Read humidity from DHT sensor
  float t = dht.readTemperature();  // Read temperature from DHT sensor
  int gasValue = analogRead(gas);   // Read analog value from gas sensor
  String airQuality;   // Variable to store air quality

  if (isnan(h) || isnan(t)) {   // Check if any reading from DHT sensor failed
    Serial.println("Failed to read from DHT sensor!");
    return;
  }

  // Determine air quality
  if (gasValue < 800) {
    airQuality = "Fresh Air";
    Serial.println("Condition: Fresh Air");
  } else {
    airQuality = "Bad Air";
    Serial.println("Condition: Bad Air");
  }

  // Print readings to serial monitor
  Serial.print("Humidity: ");
  Serial.print(h);
  Serial.print(" %\t");
  Serial.print("Temperature: ");
  Serial.print(t);
  Serial.print(" *C ");
  Serial.println("");
  Serial.print("Gas Value: ");
  Serial.print(gasValue);
  Serial.println(" PPM");

  // Display temperature on LCD
  lcd.setCursor(0, 0);
  lcd.print("Temperature: ");
  lcd.setCursor(0, 1);
  lcd.print(t);
  lcd.print(" C");
  delay(4000);
  lcd.clear();

  // Display humidity on LCD
  lcd.setCursor(0, 0);
  lcd.print("Humidity: ");
  lcd.setCursor(0, 1);
  lcd.print(h);
  lcd.print("%");
  delay(4000);
  lcd.clear();

  // Display gas value and air quality on LCD
  lcd.setCursor(0, 0);
  lcd.print("Gas Value: ");
  lcd.print(gasValue);
  lcd.setCursor(0, 1);
  lcd.print(airQuality);

  delay(4000);
  lcd.clear();

    // Send data to server if WiFi is connected
    if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    WiFiClient client;  // Create a WiFiClient object

    if (http.begin(client, serverName)) {  // Use begin(WiFiClient, url)
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");

      // Prepare POST data
      String postData = "api_key=" + apiKeyValue + "&temperature=" + String(t) + "&humidity=" + String(h) + "&gas=" + String(gasValue) + "&quality=" + String(airQuality);
      
      // Send POST request and get response
      int httpResponseCode = http.POST(postData);

      if (httpResponseCode > 0) {   // Check for successful response
        String response = http.getString();   // Get response payload
        Serial.println(httpResponseCode);     // Print HTTP response code
        Serial.println(response);             // Print response payload
      } else {
        Serial.println("Error in sending POST");  // Print error message if POST request fails
        Serial.println(httpResponseCode);   // Print HTTP error code
      }

      http.end();   // Close HTTP connection
    } else {
      Serial.println("Failed to connect to server");
    }
  }

  delay(10000);  // Send data every 10 seconds
}
