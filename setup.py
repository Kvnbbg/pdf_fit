from setuptools import setup

APP = ['pdf_size_reduce.py']
DATA_FILES = []
OPTIONS = {
    'argv_emulation': True,
    'packages': ['PyPDF2'],
}

setup(
    name='pdf_size_reducer',
    version='1.0',
    author='Kevin Marville',
    author_email='kevinmarville@gmail.com',
    description='PDF Size Reducer App',
    app=APP,
    data_files=DATA_FILES,
    options={'py2app': OPTIONS},
    setup_requires=['py2app'],
)
