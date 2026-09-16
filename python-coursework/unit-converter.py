# Sofia Williner Project: Global Unit Converter (converts between common US and international units)

fileName = '/Users/sofiawilliner/Desktop/conversion_history.txt'
print('Welcome to the Global Unit Converter!')
print('You can convert values in from following categories: Length, Mass, Temperature, and Volume')
print('Type "quit" at any time to exit the program.')

#Creation of a function that perfoms the conversions. I listed all possible units, checks the user input using if statements, and returns the conversion result.

def convertMetric(value, fromInput, toInput):

    #Length
    m = ['m', 'meter', 'meters', 'Meter', 'Meters', 'M'] #Lists of acceptable unit names for each category, allowing the user to type units in different ways (eg: "m", "meter", "Meters")
    inch = ['inch', 'inches', 'Inch', 'Inches']
    km = ['km', 'kilometer', 'kilometers', 'Km', 'Kilometer', 'Kilometers']
    miles = ['mile', 'miles', 'Mile', 'Miles']

    #Mass
    kg = ['kg', 'kilogram', 'kilograms', 'Kg', 'Kilogram', 'Kilograms']
    lb = ['lb', 'pound', 'pounds', 'Lb', 'Pound', 'Pounds']

    #Temperature
    c = ['c', 'celsius', 'Celsuis']
    f = ['f', 'fahrenheit', 'Fahrenheit']

    #Volume
    liters = ['liter', 'liters', 'l', 'L', 'Liter', 'Liters']
    tbsp = ['tbsp', 'tablespoon', 'tablespoons', 'Tbsp', 'Tablespoon', 'Tablespoons']
    cup = ['cup', 'cups', 'Cup', 'Cups']

    total = 'Invalid' #Default value before checking conditions. If none of the conversion rules match, the function returns "Invalid".
 
#Conversions were taken from https://www.unitconverters.net/common-converters.html
    
    #Length
    if fromInput in m and toInput in inch:
        total = value / 0.0254
    if fromInput in m and toInput in km:
        total = value / 1000
    if fromInput in m and toInput in miles:
        total = value / 1609.34
    if fromInput in inch and toInput in m:
        total = value * 0.0254
    if fromInput in inch and toInput in km:
        total = (value * 0.0254) / 1000
    if fromInput in inch and toInput in miles:
        total = (value * 0.0254) / 1609.34
    if fromInput in km and toInput in m:
        total = value * 1000
    if fromInput in km and toInput in inch:
        total = (value * 1000) / 0.0254
    if fromInput in km and toInput in miles:
        total = value / 1.60934
    if fromInput in miles and toInput in m:
        total = value * 1609.34
    if fromInput in miles and toInput in km:
        total = value * 1.60934
    if fromInput in miles and toInput in inch:
        total = (value * 1609.34) / 0.0254

    #Mass
    if fromInput in kg and toInput in lb:
        total = value * 2.20462
    if fromInput in lb and toInput in kg:
        total = value / 2.20462

    #Temperature
    if fromInput in c and toInput in f:
        total = (value * 9/5) + 32
    if fromInput in f and toInput in c:
        total = (value - 32) * 5/9

    #Volume
    if fromInput in liters and toInput in tbsp:
        total = value * 67.628
    if fromInput in liters and toInput in cup:
        total = value * 4.22675
    if fromInput in tbsp and toInput in liters:
        total = value / 67.628
    if fromInput in tbsp and toInput in cup:
        total = value / 16
    if fromInput in cup and toInput in liters:
        total = value / 4.22675
    if fromInput in cup and toInput in tbsp:
        total = value * 16

    return total

#Possible units for user clarification and to know what input to provide:
length_units = ['m', 'inch', 'km', 'miles']
mass_units = ['kg', 'lb']
temp_units = ['celsius', 'fahrenheit']
volume_units = ['liter', 'tbsp', 'cup']

#Creation of the main loop where the user selects a category:
category = input('Which category would you like to convert? (Length, Mass, Temperature, Volume)\n>>')
while category != 'quit':
    if category == 'Length': #Display available units to the user based on their choice
        print('Options:', length_units)
    elif category == 'Mass':
        print('Options:', mass_units)
    elif category == 'Temperature':
        print('Options:', temp_units)
    elif category == 'Volume':
        print('Options:', volume_units)
    else:
        print('Invalid category.')

    fromInput = input('Convert from which unit?\n>>')
    #Included error handling for value conversion
    value = input('Enter the value to convert:\n>>')
    while True:
        try:
            value = float(value)
            break
        except ValueError:
            print('You must enter numbers only.')
            value = input('Enter the value to convert:\n>>')
            
    toInput = input('Convert to which unit?\n>>')
    
    #Run the conversion function witht the user's input:
    result = convertMetric(float(value), fromInput, toInput)
    print(value, fromInput, '=', result, toInput)
    
    #Ask user if they want to convert another category:
    category = input('Which category would you like to convert next? (Length, Mass, Temperature, Volume) OR type "quit" to exit:\n>>')
    if category == 'quit':
        break
#Use of file I/O where the user is asked of they would like to save the last conversion:
historyFile = input('Would you like to create a file with your conversion history? Yes/No\n>>')
if historyFile == 'Yes': #Start of code from chatGPT
    #File entry would include the value from-unit and converted result
    entry = str(value) + ' ' + fromInput + ' = ' + str(result) + ' ' + toInput #End of code from chatGPT
    #Write to file using append mode
    with open(fileName, 'a', encoding='utf8') as file:
        file.write(entry + '\n')
    print('Your conversion has been saved to the Desktop.')

print('Thank you for using the Global Unit Converter! Goodbye!')
