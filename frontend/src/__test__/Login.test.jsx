import { render, screen } from '@testing-library/react';
import Login  from '../book_review/Login';
import { Provider } from 'react-redux';
import { CookiesProvider, Cookies } from 'react-cookie';
import { BrowserRouter } from 'react-router-dom';
import { store } from '../store';

//ログイン画面に必要なコンポーネントとは「メールアドレス入力欄」「パスワード入力欄」「ログインボタン」
describe("ログイン画面に必要なコンポーネントがあるか", () =>{
  beforeEach(() => { //「render」は各テスト毎に実行する必要があるから「beforEach」を使う
    render(
      <Provider store={store}>
        <CookiesProvider>
          <BrowserRouter>
            <Login />
          </BrowserRouter>      
        </CookiesProvider>
      </Provider>
      );  
  });

  test('ログインボタンが存在するか', () => {
    const LoginButton = screen.getByRole('button', { name: 'ログイン' }); //button要素を取得
    expect(LoginButton).toBeInTheDocument();
  });

  test('メールアドレス入力欄と、メールアドレスのラベルがあるか', () =>{
    const Email = screen.getByRole('textbox', { name: /useradress/i }); //input type=email要素の取得は第一引数にtextboxと書くらしい
    const EmailLabel = screen.getByText('メールアドレス');

    expect(EmailLabel).toBeInTheDocument();
    expect(Email).toBeInTheDocument();
  });

  test('パスワード入力欄と、パスワードのラベルがあるか？', () => {
    // 「input type='email'」だと「getByRole」関数の第一引数に「textbox」を書けば要素が取得できるが
    // 「input type='password'」だと「getByRole」関数じゃ取得できないらしいから、「getByLabelText」を使用
    const Password = screen.getByLabelText('UserPassword'); 
    const PasswordLabel = screen.getByText('パスワード');
    
    expect(PasswordLabel).toBeInTheDocument();
    expect(Password).toBeInTheDocument();
  });
});
  