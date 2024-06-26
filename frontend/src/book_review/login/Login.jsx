import { React, useState, useEffect } from 'react';
import { useCookies } from 'react-cookie';
import { useForm } from 'react-hook-form';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { signIn } from '../../authSlice';
import { useUrl } from '../../useUrl';
import { Header } from '../header/Header';
import './Login.scss';

export const Login = () => {
  const auth = useSelector((state) => state.auth.isSignIn);
  const dispatch = useDispatch();
  const navigate = useNavigate(); // useNavigateフックを使用
  const {
    register,
    handleSubmit,
    formState: { errors }
  } = useForm(); // バリデーションのフォームを定義。
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errorMessage, setErrorMessage] = useState();
  const loginUrl = useUrl('login'); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const [, setCookie] = useCookies();
  const handleEmailChange = (e) => setEmail(e.target.value);
  const handlePasswordChange = (e) => setPassword(e.target.value);
  const onLogIn = () => {
    axios
      .post(loginUrl, { email: email, password: password })
      .then((response) => {
        const token = response.headers.authorization;
        setCookie('token', token, { maxAge: 3600 });
        dispatch(signIn());
        navigate('/');
        window.location.reload();
      })
      .catch((err) => {
        setErrorMessage(`ログインに失敗しました。${err.response.data.message}`);
      });
  };

  useEffect(() => {
    if (auth) return navigate('/'); //「navegate」はレンダリング時に呼び出したらだめらしい。（「useEfect」内で呼び出そうとのエラーが出た）
  }, []);

  return (
    <div>
      <Header />
      <main className="Login">
        <h2>ログイン</h2>
        <p className="error-message">{errorMessage}</p>
        <form
          onSubmit={handleSubmit(onLogIn)}
          className="Login-form"
        >
          <label className="email-label">メールアドレス</label>
          <br />
          <input
            type="email"
            aria-label="UserAdress" // inputを一意にするために「aria-label」で各自名前を付ける
            {...register('email', { required: true })}
            className="email-input"
            onChange={handleEmailChange}
          />
          {/* 何か所かにある以下のような記述はバリデーションエラーが発生したときに表示されるエラー文 */}
          <p>
            {errors.email?.type === 'required' && (
              <b className="error-message">※メールアドレスを入力してください</b>
            )}
          </p>
          <br />
          <label className="password-label">パスワード</label>
          <br />
          <input
            type="password"
            aria-label="UserPassword"
            {...register('password', { required: true, minLength: { value: 5 } })}
            className="password-input"
            onChange={handlePasswordChange}
          />
          <p>
            {errors.password?.type === 'required' && (
              <b className="error-message">※パスワードを入力してください。</b>
            )}
          </p>
          <p>
            {errors.password?.type === 'minLength' && (
              <b className="error-message">※パスワードは５文字以上です</b>
            )}
          </p>
          <br />
          <button type="submit" name="login" className="Login-button">
            ログイン
          </button>
        </form>
        <Link to="/signup">新規作成</Link>
      </main>
    </div>
  );
};

export default Login;
