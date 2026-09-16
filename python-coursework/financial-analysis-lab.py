#Sofia Williner Lab 11

a = '/Users/sofiawilliner/Desktop/PythonIO/Lab XI -Stock Grapher/AAPL.csv'
m = '/Users/sofiawilliner/Desktop/PythonIO/Lab XI -Stock Grapher/MSFT.csv'

import pandas as pd
from matplotlib import pyplot as pp

apple = pd.read_csv(a, header = 0)
ms = pd.read_csv(m, header = 0)

#Load Appple
apple['Volume'] = apple['Volume'].astype(int) #redefine
apple['Date'] = pd.to_datetime(apple['Date'], format = '%Y-%m-%d')
apple = apple.set_index(['Date'])

#Load Microsoft
ms['Volume'] = ms['Volume'].astype(int)
ms['Date'] = pd.to_datetime(ms['Date'], format = '%Y-%m-%d')
ms = ms.set_index(['Date'])

#Join the data
combined = apple.join(ms, 'Date', 'inner', 'Apple', 'Ms')
combined = combined[['CloseApple', 'CloseMs']]

#How to filter
print(combined[combined['CloseApple'] > 40])

#How to aggregate
combinedYearly = combined.resample('YE').max()

y1 = combinedYearly['CloseApple'].values
y2 = combinedYearly['CloseMs'].values
x = combinedYearly.index

pp.plot(x , y1, label = 'Apple')
pp.plot(x, y2, label = 'Microsoft')
pp.legend()
pp.xlabel('Date')
pp.ylabel('Price')
pp.title('Apple vs. Microsoft')
pp.show()
