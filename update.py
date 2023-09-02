import pymysql
import requests
import json

db = pymysql.connect(
    host='127.0.0.1',
    port=3306,
    user='root',
    password='123456',
    database='book',
    charset="utf8mb4"
)

cursor = db.cursor()
sql = "select * from basic_info WHERE id > 384"
cursor.execute(sql)

fields = [field[0] for field in cursor.description]

def StrList2Json(s):
    # 字符串是列表的形式，转换成json
    if s.startswith('['):
        s = s[1:]
    if s.endswith(']'):
        s = s[:-1]
    s = s.split(',')
    s = [i.strip() for i in s]
    s = json.dumps(s, ensure_ascii=False)
    return s

books = cursor.fetchall()
for book in books:
    book = dict(zip(fields, book))
    isbn = book['isbn']
    url = f'http://localhost:3000/isbn/{isbn}'
    r = requests.get(url)
    data = r.json()
    author = data["data"]["author"]
    translator = data["data"]["translator"]
    catalog = data["data"]["catalog"]
    author = json.dumps(author, ensure_ascii=False)
    translator = json.dumps(translator, ensure_ascii=False)
    catalog = json.dumps(catalog, ensure_ascii=False)
    print(author, translator, catalog)
    sql = "UPDATE basic_info SET author = %s, translator = %s, catalog = %s WHERE id = %s"
    cursor.execute(sql, (author, translator, catalog, book['id']))
    db.commit()