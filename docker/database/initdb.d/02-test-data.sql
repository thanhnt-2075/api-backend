
TRUNCATE TABLE user;
INSERT INTO user (id, name, password, email, role) VALUES (1, 'admin', 'admin123', 'admin@localhost', 'creator');

INSERT INTO audit_log (user_id, action) VALUES (1, 'Initialised system');

INSERT INTO project (id, name, description) VALUES (1, 'Web server pentest project', 'Test project to show pentest tasks and reports');

INSERT INTO target (project_id, name, kind) VALUES
    (1, 'test.com', 'webapp'),
    (2, '127.0.0.1', 'host');

INSERT INTO vulnerability (project_id, target_id, reported_by_uid, summary, risk) VALUES
    (1, 1, 1, 'Domain about to expire', 'medium'),
    (1, 2, 1, 'Open port (tcp/22)', 'medium');

INSERT INTO task (project_id, name) VALUES
    (1, 'Run port scanner'),
    (1, 'Check domain expiration date');

INSERT INTO task_result (task_id, submitted_by_uid, output) VALUES
    (1, 1, 'tcp/22: open, tcp/80: open'),
    (1, 2, 'Domain expires in 22 days');