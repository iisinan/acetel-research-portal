import re

with open('backend/resources/views/auth/register.blade.php', 'r') as f:
    content = f.read()

content = content.replace(
    "const requiredFields = ['name', 'email', 'password', 'password_confirmation', 'matric_number', 'program_id'];",
    "const requiredFields = ['name', 'email', 'password', 'password_confirmation', 'matric_number', 'program_id', 'phone_number', 'gender', 'nationality'];"
)

with open('backend/resources/views/auth/register.blade.php', 'w') as f:
    f.write(content)
