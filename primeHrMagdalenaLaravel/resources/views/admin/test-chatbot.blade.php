<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chatbot Test Suite</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f6ff;
            padding: 20px;
            color: #0b044d;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%);
            color: #fff;
            padding: 26px;
            border-radius: 14px;
            margin-bottom: 22px;
            box-shadow: 0 10px 30px rgba(11, 4, 77, 0.22);
        }
        .header h1 { font-size: 28px; margin-bottom: 8px; }
        .header p { opacity: 0.9; font-size: 13.5px; }

        .controls {
            background: #fff;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 18px;
            border: 1px solid #eceaf8;
            box-shadow: 0 8px 24px rgba(11, 4, 77, 0.08);
        }
        .btn {
            background: #0b044d;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            margin-right: 8px;
            transition: transform 0.2s ease, background-color 0.2s ease;
        }
        .btn:hover { background: #8e1e18; transform: translateY(-1px); }
        .btn-secondary { background: #7b779d; }
        .btn-secondary:hover { background: #5d597d; }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #eceaf8;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 8px 24px rgba(11, 4, 77, 0.07);
        }
        .stat-label { font-size: 12px; color: #6f6b91; margin-bottom: 4px; }
        .stat-value { font-size: 30px; font-weight: 800; line-height: 1.1; }
        .stat-value.success { color: #15803d; }
        .stat-value.danger { color: #b91c1c; }
        .stat-value.warning { color: #d97706; }

        .test-results {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #eceaf8;
            box-shadow: 0 8px 24px rgba(11, 4, 77, 0.07);
            overflow: hidden;
        }
        .category { border-bottom: 1px solid #f0eefc; }
        .category-header {
            background: #f8f7ff;
            padding: 14px 18px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .category-header:hover { background: #f2f0fd; }
        .category-tests { display: none; }
        .category-tests.active { display: block; }

        .test-item {
            padding: 14px 18px;
            border-bottom: 1px solid #f6f5fd;
        }
        .test-item:last-child { border-bottom: none; }
        .test-question {
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #241d63;
        }
        .test-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .test-status.pending { background: #fef3c7; color: #92400e; }
        .test-status.success { background: #dcfce7; color: #166534; }
        .test-status.failed { background: #fee2e2; color: #991b1b; }
        .test-status.running { background: #dbeafe; color: #1e3a8a; }

        .test-details {
            font-size: 12.5px;
            color: #6f6b91;
            margin-top: 5px;
        }
        .test-response {
            background: #f8f7ff;
            border: 1px solid #eceaf8;
            padding: 10px;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 12px;
            color: #27214f;
            max-height: 110px;
            overflow-y: auto;
        }

        .progress-bar {
            height: 6px;
            background: #eceaf8;
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 18px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0b044d, #8e1e18);
            width: 0%;
            transition: width 0.3s;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #6f6b91;
            font-size: 13px;
        }
        .spinner {
            border: 3px solid #f1f0fd;
            border-top: 3px solid #0b044d;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            animation: spin 1s linear infinite;
            margin: 0 auto 12px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 900px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            body { padding: 12px; }
            .header { padding: 18px; }
            .header h1 { font-size: 22px; }
            .controls { display: grid; gap: 8px; }
            .btn { width: 100%; margin-right: 0; }
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 PRIME HRIS Chatbot Test Suite</h1>
            <p>Comprehensive testing of all chatbot query types and patterns</p>
        </div>

        <div class="controls">
            <button class="btn" onclick="runAllTests()">▶️ Run All Tests</button>
            <button class="btn btn-secondary" onclick="clearResults()">🗑️ Clear Results</button>
            <button class="btn btn-secondary" onclick="exportResults()">📥 Export Results</button>
        </div>

        <div class="progress-bar" id="progressBar" style="display: none;">
            <div class="progress-fill" id="progressFill"></div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Total Tests</div>
                <div class="stat-value" id="totalTests">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Passed</div>
                <div class="stat-value success" id="passedTests">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Failed</div>
                <div class="stat-value danger" id="failedTests">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Success Rate</div>
                <div class="stat-value warning" id="successRate">0%</div>
            </div>
        </div>

        <div class="test-results" id="testResults">
            <div class="loading">
                <p>Click "Run All Tests" to start testing the chatbot</p>
            </div>
        </div>
    </div>

    <script>
        const testQuestions = [
            {
                category: '1. GREETINGS',
                questions: ['Hello', 'Hi there', 'Good morning', 'Kumusta']
            },
            {
                category: '2. COUNTING - Total Employees',
                questions: ['How many employees do we have?', 'How many people work here?', 'Total number of staff', 'Ilang empleyado meron tayo?']
            },
            {
                category: '3. COUNTING - Active Employees',
                questions: ['How many active employees?', 'Count active staff', 'Ilang aktibong empleyado?']
            },
            {
                category: '4. COUNTING - Departments',
                questions: ['How many departments do we have?', 'Total number of offices']
            },
            {
                category: '5. LISTING - All Employees',
                questions: ['Show all employees', 'List all staff', 'Ipakita ang lahat ng empleyado']
            },
            {
                category: '6. LISTING - Active Employees',
                questions: ['Show active employees', 'List active staff']
            },
            {
                category: '7. LISTING - Departments',
                questions: ['List all departments', 'Show all offices']
            },
            {
                category: '8. SEARCH - By Name',
                questions: ['Find System Admin', 'Who is Administrator?', 'Search for admin']
            },
            {
                category: '9. DEPARTMENT - Head',
                questions: ['Who is the head of Mayor office?', 'Who heads the health office?']
            },
            {
                category: '10. DEPARTMENT - Personnel',
                questions: ['Who works in Mayor office?', 'Show employees in health office']
            },
            {
                category: '11. NATURAL LANGUAGE',
                questions: ['How many workers do we have?', 'Total people employed']
            },
            {
                category: '12. BILINGUAL (Taglish)',
                questions: ['How many empleyado meron?', 'Sino ang head ng Mayor office?']
            }
        ];

        let stats = { total: 0, passed: 0, failed: 0 };
        let results = [];

        async function runAllTests() {
            clearResults();
            document.getElementById('progressBar').style.display = 'block';
            
            const resultsContainer = document.getElementById('testResults');
            resultsContainer.innerHTML = '';
            
            stats = { total: 0, passed: 0, failed: 0 };
            
            for (const category of testQuestions) {
                const categoryDiv = createCategoryDiv(category);
                resultsContainer.appendChild(categoryDiv);
                
                for (const question of category.questions) {
                    stats.total++;
                    updateStats();
                    
                    const testItem = createTestItem(question);
                    categoryDiv.querySelector('.category-tests').appendChild(testItem);
                    
                    await runTest(question, testItem);
                    await sleep(200);
                }
            }
            
            document.getElementById('progressBar').style.display = 'none';
        }

        async function runTest(question, testItem) {
            const statusEl = testItem.querySelector('.test-status');
            const detailsEl = testItem.querySelector('.test-details');
            
            statusEl.textContent = 'RUNNING';
            statusEl.className = 'test-status running';
            
            try {
                const response = await fetch('/api/chatbot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message: question })
                });

                const data = await response.json();
                
                if (response.ok && data.status === 'success') {
                    statusEl.textContent = 'SUCCESS';
                    statusEl.className = 'test-status success';
                    stats.passed++;
                    
                    const responseText = data.response.substring(0, 150) + (data.response.length > 150 ? '...' : '');
                    detailsEl.innerHTML = `
                        <strong>Query Type:</strong> ${data.query_type || 'N/A'}<br>
                        <div class="test-response">${responseText}</div>
                    `;
                    
                    results.push({ question, status: 'success', queryType: data.query_type, response: data.response });
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            } catch (error) {
                statusEl.textContent = 'FAILED';
                statusEl.className = 'test-status failed';
                stats.failed++;
                
                detailsEl.innerHTML = `<strong>Error:</strong> ${error.message}`;
                results.push({ question, status: 'failed', error: error.message });
            }
            
            updateStats();
        }

        function createCategoryDiv(category) {
            const div = document.createElement('div');
            div.className = 'category';
            div.innerHTML = `
                <div class="category-header" onclick="toggleCategory(this)">
                    <span>${category.category}</span>
                    <span>▼</span>
                </div>
                <div class="category-tests active"></div>
            `;
            return div;
        }

        function createTestItem(question) {
            const div = document.createElement('div');
            div.className = 'test-item';
            div.innerHTML = `
                <div class="test-question">
                    <span class="test-status pending">PENDING</span>
                    <span>"${question}"</span>
                </div>
                <div class="test-details">Waiting to run...</div>
            `;
            return div;
        }

        function toggleCategory(header) {
            const tests = header.nextElementSibling;
            tests.classList.toggle('active');
            header.querySelector('span:last-child').textContent = tests.classList.contains('active') ? '▼' : '▶';
        }

        function updateStats() {
            document.getElementById('totalTests').textContent = stats.total;
            document.getElementById('passedTests').textContent = stats.passed;
            document.getElementById('failedTests').textContent = stats.failed;
            
            const rate = stats.total > 0 ? Math.round((stats.passed / stats.total) * 100) : 0;
            document.getElementById('successRate').textContent = rate + '%';
            
            const progress = stats.total > 0 ? (stats.passed + stats.failed) / stats.total * 100 : 0;
            document.getElementById('progressFill').style.width = progress + '%';
        }

        function clearResults() {
            document.getElementById('testResults').innerHTML = '<div class="loading"><p>Click "Run All Tests" to start testing the chatbot</p></div>';
            stats = { total: 0, passed: 0, failed: 0 };
            results = [];
            updateStats();
        }

        function exportResults() {
            const dataStr = JSON.stringify(results, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'chatbot-test-results.json';
            link.click();
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
    </script>
</body>
</html>