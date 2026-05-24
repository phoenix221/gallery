CREATE TABLE images(
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) COMMENT 'имя файла в S3',
    original_name VARCHAR(255) COMMENT 'оригинальное имя файла',
    size INT COMMENT 'размер файла в байтах',
    mime_type VARCHAR(100) COMMENT 'MIMEтип файла',
    uploaded_at DATETIME COMMENT 'дата загрузки',
    s3_url TEXT COMMENT 'публичный URL изображения в S3'
);