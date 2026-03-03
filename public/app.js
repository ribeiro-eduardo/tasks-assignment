const API = '';

let usersCache = [];

// ── Navigation ──

document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const section = link.dataset.section;
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        link.classList.add('active');
        document.querySelectorAll('.section').forEach(s => s.classList.add('hidden'));
        document.getElementById('section-' + section).classList.remove('hidden');

        if (section === 'users') loadUsers();
        if (section === 'tasks') loadTasks();
    });
});

// ── Toast ──

function toast(msg, isError) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'toast show' + (isError ? ' error' : '');
    setTimeout(() => el.className = 'toast hidden', 2500);
}

// ── Form helpers ──

function showForm(type) {
    document.getElementById(type + '-form').classList.remove('hidden');
    document.getElementById(type + '-form-title').textContent = 'New ' + capitalize(type);
    document.getElementById(type + '-id').value = '';
    clearFormInputs(type);
    if (type === 'task') loadUserOptions();
}

function hideForm(type) {
    document.getElementById(type + '-form').classList.add('hidden');
}

function clearFormInputs(type) {
    if (type === 'user') {
        document.getElementById('user-name').value = '';
        document.getElementById('user-email').value = '';
    } else {
        document.getElementById('task-title').value = '';
        document.getElementById('task-description').value = '';
        document.getElementById('task-status').value = 'todo';
        document.getElementById('task-user').value = '';
    }
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// ── API helpers ──

async function api(method, path, body) {
    const opts = {
        method,
        headers: { 'Content-Type': 'application/json' },
    };
    if (body) opts.body = JSON.stringify(body);

    const res = await fetch(API + path, opts);
    const json = await res.json();

    if (!res.ok) {
        throw new Error(json.error || json.message || 'Request failed');
    }
    return json;
}

// ── Users ──

async function loadUsers() {
    try {
        const json = await api('GET', '/users');
        usersCache = json.data || [];
        renderUsers(usersCache);
    } catch (e) {
        toast(e.message, true);
    }
}

function renderUsers(users) {
    const tbody = document.getElementById('users-body');
    const table = document.getElementById('users-table');
    const empty = document.getElementById('users-empty');

    if (!users || users.length === 0) {
        table.classList.add('hidden');
        empty.classList.remove('hidden');
        tbody.innerHTML = '';
        return;
    }

    table.classList.remove('hidden');
    empty.classList.add('hidden');

    tbody.innerHTML = users.map(u => `
        <tr>
            <td>${u.id}</td>
            <td>${esc(u.name)}</td>
            <td>${esc(u.email)}</td>
            <td>${formatDate(u.created_at)}</td>
            <td>
                <div class="actions">
                    <button class="btn btn-sm btn-edit" onclick="editUser(${u.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id})">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

async function saveUser() {
    const id = document.getElementById('user-id').value;
    const data = {
        name: document.getElementById('user-name').value.trim(),
        email: document.getElementById('user-email').value.trim(),
    };

    try {
        if (id) {
            await api('PUT', '/users/' + id, data);
            toast('User updated');
        } else {
            await api('POST', '/users', data);
            toast('User created');
        }
        hideForm('user');
        loadUsers();
    } catch (e) {
        toast(e.message, true);
    }
}

function editUser(id) {
    const user = usersCache.find(u => u.id === id);
    if (!user) return;

    document.getElementById('user-id').value = user.id;
    document.getElementById('user-name').value = user.name;
    document.getElementById('user-email').value = user.email;
    document.getElementById('user-form-title').textContent = 'Edit User';
    document.getElementById('user-form').classList.remove('hidden');
}

async function deleteUser(id) {
    if (!confirm('Delete this user?')) return;
    try {
        await api('DELETE', '/users/' + id);
        toast('User deleted');
        loadUsers();
    } catch (e) {
        toast(e.message, true);
    }
}

// ── Tasks ──

let tasksCache = [];

async function loadUserOptions() {
    try {
        const json = await api('GET', '/users');
        usersCache = json.data || [];
        const select = document.getElementById('task-user');
        select.innerHTML = usersCache.map(u =>
            `<option value="${u.id}">${esc(u.name)}</option>`
        ).join('');
    } catch (e) {
        toast(e.message, true);
    }
}

function getUserName(id) {
    const u = usersCache.find(u => u.id == id);
    return u ? u.name : '#' + id;
}

function statusLabel(status) {
    const labels = { todo: 'Todo', in_progress: 'In Progress', done: 'Done' };
    return labels[status] || status;
}

function renderTasks(tasks) {
    const tbody = document.getElementById('tasks-body');
    const table = document.getElementById('tasks-table');
    const empty = document.getElementById('tasks-empty');

    if (!tasks || tasks.length === 0) {
        table.classList.add('hidden');
        empty.classList.remove('hidden');
        tbody.innerHTML = '';
        return;
    }

    table.classList.remove('hidden');
    empty.classList.add('hidden');

    tbody.innerHTML = tasks.map(t => `
        <tr>
            <td>${t.id}</td>
            <td>${esc(t.title)}</td>
            <td><span class="badge badge-${t.status}">${statusLabel(t.status)}</span></td>
            <td>${esc(getUserName(t.id_assigned_user))}</td>
            <td>${formatDate(t.created_at)}</td>
            <td>
                <div class="actions">
                    <button class="btn btn-sm btn-edit" onclick="editTask(${t.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteTask(${t.id})">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

async function loadTasks() {
    try {
        const json = await api('GET', '/tasks');
        tasksCache = json.data || [];

        const usersJson = await api('GET', '/users');
        usersCache = usersJson.data || [];

        renderTasks(tasksCache);
    } catch (e) {
        toast(e.message, true);
    }
}

async function saveTask() {
    const id = document.getElementById('task-id').value;
    const data = {
        title: document.getElementById('task-title').value.trim(),
        description: document.getElementById('task-description').value.trim(),
        status: document.getElementById('task-status').value,
        id_assigned_user: parseInt(document.getElementById('task-user').value),
    };

    try {
        if (id) {
            await api('PUT', '/tasks/' + id, data);
            toast('Task updated');
        } else {
            await api('POST', '/tasks', data);
            toast('Task created');
        }
        hideForm('task');
        loadTasks();
    } catch (e) {
        toast(e.message, true);
    }
}

function editTask(id) {
    const task = tasksCache.find(t => t.id === id);
    if (!task) return;

    loadUserOptions().then(() => {
        document.getElementById('task-id').value = task.id;
        document.getElementById('task-title').value = task.title;
        document.getElementById('task-description').value = task.description || '';
        document.getElementById('task-status').value = task.status;
        document.getElementById('task-user').value = task.id_assigned_user;
        document.getElementById('task-form-title').textContent = 'Edit Task';
        document.getElementById('task-form').classList.remove('hidden');
    });
}

async function deleteTask(id) {
    if (!confirm('Delete this task?')) return;
    try {
        await api('DELETE', '/tasks/' + id);
        toast('Task deleted');
        loadTasks();
    } catch (e) {
        toast(e.message, true);
    }
}

// ── Utils ──

function esc(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatDate(str) {
    if (!str) return '';
    const d = new Date(str);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ── Init ──

loadTasks();
