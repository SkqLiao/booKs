import openai
import json
import pymysql

openai.api_base = 'https://api.closeai-proxy.xyz/v1'
openai.api_key = 'sk-lWdqFP59T0cqMkMov0bPR0TJiQ9nnw7FPmr8CVl9Ww6xLCjL'

db = pymysql.connect(
    host='127.0.0.1',
    port=3306,
    user='root',
    password='123456',
    database='book',
    charset="utf8mb4"
)

cursor = db.cursor()
sql = "select * from basic_info"
cursor.execute(sql)

fields = [field[0] for field in cursor.description]
messages = [
    {"role": "system", "content": "You are an AI assistant, next you need to convert the writer's name I provided into standard format. The requirements are as follows: if modern Chinese, it's his full name, e.g. 李泽厚; if ancient Chinese, it's a [dynasty] name, e.g. [唐] 李白; otherwise the format is a [country] name(original real name), e.g. [美国] 乔治·奥威尔(George Orwell), with the Chinese name being the most common official translation. I will give you several names at a time, separated by , ."}
]
authors = []

books = cursor.fetchall()
for book in books:
    book = dict(zip(fields, book))
    authors += (json.loads(book['author']))
    if len(authors) > 50:
        messages.append({"role": "user", "content": ",".join(authors)})
        chat = openai.ChatCompletion.create(model="gpt-3.5-turbo", messages=messages)
        reply = chat.choices[0].message.content
        new_authors = reply.split(',')
        new_authors = [i.strip() for i in new_authors]
        for i in range(len(authors)):
            print(authors[i], "=>", new_authors[i])
        messages = [
            {"role": "system", "content": "You are an AI assistant, next you need to convert the writer's name I provided into standard format. The requirements are as follows: if modern Chinese, it's his full name, e.g. 李泽厚; if ancient Chinese, it's a [dynasty] name, e.g. [唐] 李白; otherwise the format is a [country] name(original real name), e.g. [美国] 乔治·奥威尔(George Orwell), with the Chinese name being the most common official translation. I will give you several names at a time, separated by , ."}
        ]
        author = []
        break
    
