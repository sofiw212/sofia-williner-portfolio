#Sofia Williner Lab 10
from bs4 import BeautifulSoup
from urllib.request import urlopen
import pandas as pd
f = '/Users/sofiawilliner/Desktop/PythonIO/GoodReadsWebsite.txt'
"""
url = 'https://www.goodreads.com/list/show/143500.Best_Books_of_the_Decade_2020_s?ref=ls_fl_0_seeall'
html = urlopen(url)
soup = BeautifulSoup(html, 'html.parser')

with open(f, 'w', encoding = 'utf8') as file:
    file.write(soup.prettify())

"""
with open(f, 'r', encoding = 'utf8') as file:
    html = file.read()
soup = BeautifulSoup(html, 'html.parser')

bookList = []
books = soup.find_all('tr', itemtype = 'http://schema.org/Book')

for book in books:
    title = book.find(class_ = 'bookTitle')
    title = title.text.strip()
    try:
        author = book.find(class_ = 'authorName')
        author = author.text.strip()
    except:
        author = 'Unknown'
    rating = book.find(class_='minirating').text.strip()
    row = [title, author, rating]
    bookList.append(row)
df = pd.DataFrame(bookList)
print(df)
