-- ALTERAÇÕES DESSE ARQUIVO JA ESTÃO NO BANCO EM PRODUÇÃO
ALTER TABLE employees
ADD COLUMN bi_document VARCHAR(30),
ADD COLUMN birth_date DATE,
ADD COLUMN contract_type VARCHAR(50),
ADD COLUMN admission_date DATE;

UPDATE employees SET
  bi_document = '13295932786',
  birth_date = '1993-01-01',
  contract_type = 'Efetivo',
  admission_date = '2022-03-05'
WHERE id = 11;

CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    suggested_salary DECIMAL(10,2) DEFAULT 0
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    company_id INT NOT NULL,
    date DATE NOT NULL,
    type ENUM('presença', 'falta', 'atestado', 'folga') DEFAULT 'presença',
    justification TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
